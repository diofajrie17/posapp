<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Member;
use App\Models\SalesItem;
use App\Models\SalesTransaction;
use App\Models\StockMovement; // dari modul inventori (jika dipakai)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SalesTransactionController extends Controller
{
    public function __construct()
    {
        // Hapus baris middleware ini jika kamu belum pakai Spatie Permission
        $this->middleware('permission:transactions.view')->only(['index','show','receipt']);
        $this->middleware('permission:transactions.create')->only(['create','store']);
        $this->middleware('permission:transactions.delete')->only(['destroy']);
        $this->middleware('permission:transactions.reprint')->only(['receipt']);
    }

    /**
     * List transaksi + filter.
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
     * Form POS (buat transaksi).
     */
    public function create()
    {
        return Inertia::render('Transactions/Create', [
            'products' => Product::orderBy('name')->get(['id','name','price','stock','unit']),
            'members'  => Member::orderBy('full_name')->get(['id','full_name']),
        ]);
    }

    /**
     * Simpan transaksi POS: subtotal, diskon, paid/change, cek stok + lock, movement OUT, redirect ke struk.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.quantity'      => 'required|integer|min:1',
            'items.*.price_each'    => 'required|numeric|min:0',
            'payment_type'          => 'required|string', // Cash / QR / Transfer
            'member_id'             => 'nullable|exists:members,id',
            'discount_type'         => 'nullable|in:fixed,percent',
            'discount_value'        => 'nullable|numeric|min:0',
            'paid_amount'           => 'nullable|numeric|min:0',
            'notes'                 => 'nullable|string|max:255',
        ]);

        // 1) Subtotal & diskon
        $subtotal = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['price_each']);

        $discountType   = $request->discount_type;
        $discountValue  = $request->discount_value ?? 0;
        $discountAmount = 0.0;

        if ($discountType === 'fixed') {
            $discountAmount = min((float)$discountValue, (float)$subtotal);
        } elseif ($discountType === 'percent') {
            $percent        = max(0, min(100, (float)$discountValue));
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
            // Non-cash: abaikan paid/change
            $paid = null;
            $change = null;
        }

        // 3) Transaksi DB + lock stok
        DB::transaction(function () use ($request, $subtotal, $discountType, $discountValue, $discountAmount, $total, $paid, $change) {

            // Lock semua produk yang terlibat
            $productIds = collect($request->items)->pluck('product_id')->unique()->values();
            /** @var \Illuminate\Support\Collection<int,\App\Models\Product> $products */
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            // Cek stok cukup
            $errors = [];
            foreach ($request->items as $i => $item) {
                $p = $products[$item['product_id']];
                if ($p->stock < $item['quantity']) {
                    $errors["items.$i.quantity"] = "Stok {$p->name} tidak cukup. Stok tersedia: {$p->stock}.";
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

            // Simpan item + kurangi stok + catat movement OUT
            foreach ($request->items as $item) {
                SalesItem::create([
                    'transaction_id' => $trx->id,
                    'product_id'     => $item['product_id'],
                    'quantity'       => $item['quantity'],
                    'price_each'     => $item['price_each'],
                ]);

                // Kurangi stok
                Product::whereKey($item['product_id'])->decrement('stock', $item['quantity']);

                // Movement OUT (jika model tersedia)
                if (class_exists(StockMovement::class)) {
                    StockMovement::create([
                        'product_id' => $item['product_id'],
                        'direction'  => 'OUT',
                        'quantity'   => $item['quantity'],
                        'source'     => 'pos',
                        'source_id'  => $trx->id,
                        'note'       => 'POS sale',
                        'moved_at'   => now(),
                    ]);
                }
            }

            // Redirect langsung ke struk
            redirect()->route('transactions.receipt', $trx->id)->send();
        });

        // Fallback
        return redirect()->route('transactions.index');
    }

    /**
     * Detail transaksi.
     */
    public function show(SalesTransaction $salesTransaction)
    {
        $salesTransaction->load(['items.product:id,name,unit', 'member:id,full_name']);
        return Inertia::render('Transactions/Show', [
            'transaction' => $salesTransaction,
        ]);
    }

    /**
     * Halaman struk.
     */
    public function receipt(SalesTransaction $salesTransaction)
    {
        $salesTransaction->load(['items.product:id,name,unit', 'member:id,full_name,phone']);

        return Inertia::render('Transactions/Receipt', [
            'transaction' => $salesTransaction,
            'meta' => [
                'store_name' => config('app.name', 'GYM KASIR'),
                'address'    => config('app.store_address', 'Jl. Contoh No. 123, Kota'),
                'cashier'    => auth()->user()->name ?? 'Kasir',
                'trx_code'   => 'TRX-' . now()->format('Ymd') . '-' . str_pad($salesTransaction->id, 5, '0', STR_PAD_LEFT),
                'printed_at' => now()->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * Hapus transaksi: reversal stok + movement IN (opsional).
     * Batasi permission ini untuk Admin saja.
     */
    public function destroy(SalesTransaction $salesTransaction)
    {
        DB::transaction(function () use ($salesTransaction) {
            $salesTransaction->load('items');

            foreach ($salesTransaction->items as $it) {
                // Kembalikan stok
                Product::whereKey($it->product_id)->increment('stock', $it->quantity);

                // Movement IN (reversal)
                if (class_exists(StockMovement::class)) {
                    StockMovement::create([
                        'product_id' => $it->product_id,
                        'direction'  => 'IN',
                        'quantity'   => $it->quantity,
                        'source'     => 'pos_void',
                        'source_id'  => $salesTransaction->id,
                        'note'       => 'Reversal POS (void)',
                        'moved_at'   => now(),
                    ]);
                }
            }

            // Hapus item & transaksi
            $salesTransaction->items()->delete();
            $salesTransaction->delete();
        });

        return redirect()->route('transactions.index')->with('message', 'Transaksi dihapus & stok dipulihkan.');
    }
}
