<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Member;
use App\Models\SalesItem;
use App\Models\SalesTransaction;
use App\Models\StockMovement;          // dari modul inventori
use App\Services\FifoInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class TransactionController extends Controller
{
    protected $fifoService;

    public function __construct(FifoInventoryService $fifoService)
    {
        $this->fifoService = $fifoService;
        
        // Jika memakai Spatie Permission, aktifkan ini
        $this->middleware('permission:transactions.view')->only(['index','show','receipt']);
        $this->middleware('permission:transactions.create')->only(['create','store']);
        $this->middleware('permission:transactions.delete')->only(['destroy']);
        $this->middleware('permission:transactions.reprint')->only(['receipt']);
    }

    /**
     * List transaksi dengan filter tanggal/metode/member.
     */
    public function index(Request $request)
    {
        $dateFrom    = $request->query('date_from');
        $dateTo      = $request->query('date_to');
        $paymentType = $request->query('payment_type');
        $memberId    = $request->query('member_id');

        $q = SalesTransaction::with('member:id,full_name')
            ->orderByDesc('date_time');

        if ($dateFrom)    $q->whereDate('date_time', '>=', $dateFrom);
        if ($dateTo)      $q->whereDate('date_time', '<=', $dateTo);
        if ($paymentType) $q->where('payment_type', $paymentType);
        if ($memberId)    $q->where('member_id', $memberId);

        $transactions = $q->paginate(20)->withQueryString();

        return Inertia::render('Transactions/Index', [
            'filters'      => compact('dateFrom','dateTo','paymentType','memberId'),
            'transactions' => $transactions,
            'members'      => Member::orderBy('full_name')->get(['id','full_name']),
        ]);
    }

    /**
     * Form transaksi POS.
     */
    public function create()
    {
        $products = Product::with(['unit:id,name,symbol', 'baseUnit:id,name,symbol'])
            ->where('is_active', true) // Only show active products
            ->where('stock', '>', 0) // Only show products with stock available
            ->orderBy('name')
            ->get([
                'id', 'name', 'price', 'stock', 'unit', 'unit_id', 'unit_quantity', 'base_unit_id',
                'base_unit_price', 'derived_unit_price', 'min_stock'
            ]);

        return Inertia::render('Transactions/Create', [
            'products' => $products,
            'members'  => Member::orderBy('full_name')->get(['id','full_name']),
        ]);
    }

    /**
     * Simpan transaksi POS + cek stok + movement.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items'                      => 'required|array|min:1',
            'items.*.product_id'         => 'required|exists:products,id',
            'items.*.quantity'           => 'required|numeric|min:0.001',
            'items.*.price_each'         => 'required|numeric|min:0',
            'items.*.sell_in_base_unit'  => 'nullable|boolean',
            'payment_type'               => 'required|string', // Cash / QR / Transfer
            'member_id'                  => 'nullable|exists:members,id',
            'discount_type'              => 'nullable|in:fixed,percent',
            'discount_value'             => 'nullable|numeric|min:0',
            'paid_amount'                => 'nullable|numeric|min:0',
            'notes'                      => 'nullable|string|max:255',
        ]);

        // 1) Hitung subtotal & diskon
        $subtotal = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['price_each']);

        $discountType   = $request->discount_type;
        $discountValue  = $request->discount_value ?? 0;
        $discountAmount = 0.0;

        if ($discountType === 'fixed') {
            $discountAmount = min((float)$discountValue, (float)$subtotal);
        } elseif ($discountType === 'percent') {
            $percent       = max(0, min(100, (float)$discountValue));
            $discountAmount = round($subtotal * ($percent / 100), 2);
        }

        $total = max(0, $subtotal - $discountAmount);

        // 2) Paid & Change (Cash only)
        $paid   = $request->payment_type === 'Cash' ? ($request->paid_amount !== null ? (float)$request->paid_amount : null) : null;
        $change = $request->payment_type === 'Cash' && $paid !== null ? max(0, $paid - $total) : null;

        // Validasi keras untuk Cash
        if ($request->payment_type === 'Cash') {
            if ($paid === null) {
                throw ValidationException::withMessages(['paid_amount' => 'Nilai dibayar wajib diisi untuk pembayaran Cash.']);
            }
            if ($paid < $total) {
                throw ValidationException::withMessages(['paid_amount' => 'Jumlah dibayar kurang dari total.']);
            }
        } else {
            // Non-cash: abaikan paid & change
            $paid = null;
            $change = null;
        }

        // 3) Transaksi DB + lock stok
        DB::transaction(function () use ($request, $subtotal, $discountType, $discountValue, $discountAmount, $total, $paid, $change) {

            // Lock semua produk yang terlibat
            $productIds = collect($request->items)->pluck('product_id')->unique()->values();
            /** @var \Illuminate\Support\Collection<int,\App\Models\Product> $products */
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            // Cek stok cukup (convert to base units for checking)
            $errors = [];
            foreach ($request->items as $i => $item) {
                $p = $products[$item['product_id']];
                $sellInBaseUnit = $item['sell_in_base_unit'] ?? false;
                
                // Calculate quantity in base units using Product model method
                $quantityInBaseUnits = $p->convertToBaseUnits($item['quantity'], $sellInBaseUnit);
                
                // Get available stock from FIFO batches (or product stock as fallback)
                $availableStock = $this->fifoService->getAvailableStock($p->id);
                if ($availableStock == 0) {
                    $availableStock = $p->stock; // Fallback to product stock if no batches
                }
                
                $unitName = $p->baseUnit ? $p->baseUnit->name : ($p->unit ? $p->unit->name : 'unit');
                if ($availableStock < $quantityInBaseUnits) {
                    $errors["items.$i.quantity"] = "Stok {$p->name} tidak cukup. Stok tersedia: {$availableStock} {$unitName}.";
                }
            }
            if (!empty($errors)) {
                throw ValidationException::withMessages($errors);
            }

            // Buat transaksi
            $trx = SalesTransaction::create([
                'member_id'       => $request->member_id,
                'is_daily_guest'  => !$request->member_id,
                'payment_type'    => $request->payment_type,
                'subtotal_amount' => $subtotal,
                'discount_type'   => $discountType,
                'discount_value'  => $discountValue,
                'discount_amount' => $discountAmount,
                'total_amount'    => $total,
                'paid_amount'     => $paid,
                'change_amount'   => $change,
                'notes'           => $request->notes,
                'date_time'       => now(),
            ]);

            // Simpan item + kurangi stok + catat movement OUT + FIFO allocation
            foreach ($request->items as $item) {
                $product = $products[$item['product_id']];
                $sellInBaseUnit = $item['sell_in_base_unit'] ?? false;
                
                // Calculate quantity in base units using Product model method
                $quantityInBaseUnits = $product->convertToBaseUnits($item['quantity'], $sellInBaseUnit);
                
                // Create sales item
                $salesItem = SalesItem::create([
                    'transaction_id' => $trx->id,
                    'product_id'     => $item['product_id'],
                    'quantity'       => $item['quantity'],
                    'price_each'     => $item['price_each'],
                ]);

                // === FIFO ALLOCATION: Allocate from oldest batches first ===
                try {
                    // Check if product has any batches
                    $availableStock = $this->fifoService->getAvailableStock($item['product_id']);
                    
                    // If no batches exist, create an initial batch from current stock
                    if ($availableStock == 0 && $product->stock > 0) {
                        $this->fifoService->addBatch(
                            productId: $item['product_id'],
                            quantityInBaseUnit: $product->stock,
                            costPerBaseUnit: $product->cost_price ?? 0,
                            supplier: 'System Migration',
                            notes: 'Auto-created batch from existing stock'
                        );
                    }
                    
                    // Now allocate from batches
                    $this->fifoService->allocateStock($salesItem, $quantityInBaseUnits);
                } catch (\Exception $e) {
                    // If FIFO allocation fails, throw error
                    throw ValidationException::withMessages([
                        'items' => "FIFO allocation failed for {$product->name}: {$e->getMessage()}"
                    ]);
                }

                // Update product stock (will be recalculated from batches)
                $this->fifoService->updateProductStock($item['product_id']);

                // Movement OUT (record in base units)
                if (class_exists(StockMovement::class)) {
                    $unitSold = $sellInBaseUnit 
                        ? ($product->baseUnit->name ?? 'unit')
                        : ($product->unit->name ?? 'unit');
                    
                    StockMovement::create([
                        'product_id' => $item['product_id'],
                        'direction'  => 'OUT',
                        'quantity'   => $quantityInBaseUnits,
                        'source'     => 'pos',
                        'source_id'  => $trx->id,
                        'note'       => "POS sale: {$item['quantity']} {$unitSold} (Base: {$quantityInBaseUnits} " . ($product->baseUnit->name ?? 'unit') . ")",
                        'moved_at'   => now(),
                    ]);
                }
            }

            // Redirect langsung ke struk
            redirect()->route('transactions.receipt', $trx->id)->send();
        });

        // Fallback (kalau redirect di dalam transaction tidak dieksekusi oleh server handler)
        return redirect()->route('transactions.index');
    }

    /**
     * Detail transaksi (view biasa).
     */
    public function show(SalesTransaction $transaction)
    {
        $transaction->load(['items.product:id,name,unit', 'member:id,full_name']);
        return Inertia::render('Transactions/Show', [
            'transaction' => $transaction,
        ]);
    }

    /**
     * Halaman struk (layout thermal-friendly di Vue).
     */
    public function receipt(SalesTransaction $transaction)
    {
        $transaction->load(['items.product:id,name,unit', 'member:id,full_name,phone']);

        return Inertia::render('Transactions/Receipt', [
            'transaction' => $transaction,
            'meta' => [
                'store_name' => config('app.name', 'GYM KASIR'),
                'address'    => config('app.store_address', 'Jl. Contoh No. 123, Kota'),
                'cashier'    => auth()->user()->name ?? 'Kasir',
                'trx_code'   => 'TRX-' . now()->format('Ymd') . '-' . str_pad($transaction->id, 5, '0', STR_PAD_LEFT),
                'printed_at' => now()->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * Hapus transaksi (opsional): reversal stok + movement IN.
     * Batasi permission ini untuk Admin saja!
     */
    public function destroy(SalesTransaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            $transaction->load('items');

            // Kembalikan stok & log movement IN + deallocate FIFO
            foreach ($transaction->items as $it) {
                // Deallocate from batches (returns stock to original batches)
                $this->fifoService->deallocateStock($it);

                if (class_exists(StockMovement::class)) {
                    StockMovement::create([
                        'product_id' => $it->product_id,
                        'direction'  => 'IN',
                        'quantity'   => $it->quantity,
                        'source'     => 'pos_void',
                        'source_id'  => $transaction->id,
                        'note'       => 'Reversal POS (void)',
                        'moved_at'   => now(),
                    ]);
                }
            }

            // Hapus item lalu transaksi
            $transaction->items()->delete();
            $transaction->delete();
        });

        return redirect()->route('transactions.index')->with('message', 'Transaksi dihapus & stok dipulihkan.');
    }
}
