<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\InventoryBatch;
use App\Models\StockMovement;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:purchases.view')->only(['index', 'show']);
        $this->middleware('permission:purchases.create')->only(['create', 'store']);
        $this->middleware('permission:purchases.delete')->only(['destroy']);
    }

    /**
     * List all purchases with filters
     */
    public function index(Request $request)
    {
        $query = Purchase::with('creator:id,name')
            ->orderByDesc('purchase_date')
            ->orderByDesc('id');

        // Filters
        if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->date_to);
        }
        if ($request->filled('supplier')) {
            $query->where('supplier_name', 'like', '%' . $request->supplier . '%');
        }

        $purchases = $query->paginate(20)->withQueryString();

        return Inertia::render('Purchases/Index', [
            'purchases' => $purchases,
            'filters' => $request->only(['date_from', 'date_to', 'supplier']),
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $products = Product::with(['unit', 'baseUnit'])
            ->orderBy('name')
            ->get(['id', 'name', 'stock', 'unit_id', 'base_unit_id', 'cost_price']);

        // Get all units grouped by type (for custom conversion input)
        $units = Unit::orderBy('type')->orderBy('name')->get(['id', 'name', 'symbol', 'type']);

        return Inertia::render('Purchases/Create', [
            'products' => $products,
            'units' => $units,
            'purchaseNumber' => Purchase::generatePurchaseNumber(),
        ]);
    }

    /**
     * Store new purchase and update stock with FIFO
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_name' => 'required|string|max:255',
            'supplier_phone' => 'nullable|string|max:50',
            'supplier_address' => 'nullable|string',
            'purchase_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.unit_name' => 'required|string',  // NEW: Custom unit name
            'items.*.unit_conversion' => 'required|numeric|min:0.0001',  // NEW: Custom conversion
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request) {
            // Calculate total
            $totalAmount = collect($request->items)->sum(function ($item) {
                return $item['quantity'] * $item['unit_cost'];
            });

            // Create purchase
            $purchase = Purchase::create([
                'purchase_number' => Purchase::generatePurchaseNumber(),
                'supplier_name' => $request->supplier_name,
                'supplier_phone' => $request->supplier_phone,
                'supplier_address' => $request->supplier_address,
                'purchase_date' => $request->purchase_date,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            // Lock products and update stock
            $productIds = collect($request->items)->pluck('product_id')->unique();
            $products = Product::with('unit')->whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            foreach ($request->items as $item) {
                $product = $products[$item['product_id']];
                
                $quantity = $item['quantity'];
                $unitCost = $item['unit_cost'];
                $unitName = $item['unit_name'];
                $unitConversion = $item['unit_conversion']; // Custom conversion factor
                $subtotal = $quantity * $unitCost;

                // Convert to base units using CUSTOM conversion
                $quantityInBase = $quantity * $unitConversion;
                $unitCostInBase = $unitConversion > 0 ? $unitCost / $unitConversion : $unitCost;

                // Find unit_id from unit_name
                $unit = Unit::where('name', $unitName)->first();
                $unitId = $unit ? $unit->id : $product->unit_id; // Fallback to product's unit_id

                // Create purchase item with custom unit info
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'unit_id' => $unitId, // ADD THIS
                    'unit_name' => $unitName,
                    'unit_conversion' => $unitConversion,
                    'quantity' => $quantity,
                    'quantity_in_base_unit' => $quantityInBase,
                    'unit_cost' => $unitCost,
                    'base_quantity' => $quantityInBase,  // For backward compatibility
                    'base_unit_cost' => $unitCostInBase,  // For backward compatibility
                    'subtotal' => $subtotal,
                ]);

                // Update product stock (in base units)
                $oldStock = $product->stock;
                $newStock = $oldStock + $quantityInBase;
                
                // Update average cost using weighted average
                $oldCost = $product->average_cost ?? $product->cost_price ?? 0;
                $oldValue = $oldStock * $oldCost;
                $newValue = $quantityInBase * $unitCostInBase;
                $averageCost = $newStock > 0 ? ($oldValue + $newValue) / $newStock : $unitCostInBase;

                $product->update([
                    'stock' => $newStock,
                    'last_purchase_cost' => $unitCostInBase,
                    'average_cost' => $averageCost,
                ]);

                // Create inventory batch for FIFO
                InventoryBatch::create([
                    'product_id' => $item['product_id'],
                    'purchase_id' => $purchase->id,
                    'quantity_remaining' => $quantityInBase,
                    'unit_cost' => $unitCostInBase,
                    'purchased_at' => $request->purchase_date,
                ]);

                // Log stock movement
                $baseUnitName = $product->unit->name ?? 'unit';
                $note = "Purchase: {$quantity} {$unitName} @ Rp " . number_format($unitCost, 0, ',', '.');
                if ($unitConversion > 1) {
                    $note .= " (1 {$unitName} = {$unitConversion} {$baseUnitName}, total = {$quantityInBase} {$baseUnitName})";
                }
                
                StockMovement::create([
                    'product_id' => $item['product_id'],
                    'direction' => 'IN',
                    'quantity' => $quantityInBase,
                    'source' => 'purchase',
                    'source_id' => $purchase->id,
                    'note' => $note,
                    'moved_at' => now(),
                ]);
            }
        });

        return redirect()->route('purchases.index')
            ->with('message', 'Pembelian berhasil disimpan dan stok diperbarui.');
    }

    /**
     * Show purchase detail
     */
    public function show(Purchase $purchase)
    {
        $purchase->load([
            'items.product:id,name',
            'items.unit:id,name,symbol',
            'creator:id,name'
        ]);
        
        return Inertia::render('Purchases/Show', [
            'purchase' => $purchase,
        ]);
    }

    /**
     * Delete purchase and reverse stock
     */
    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            $purchase->load('items.product');

            foreach ($purchase->items as $item) {
                // Reverse stock
                $product = Product::lockForUpdate()->find($item->product_id);
                $product->decrement('stock', $item->base_quantity);

                // Remove associated batches
                InventoryBatch::where('purchase_id', $purchase->id)
                    ->where('product_id', $item->product_id)
                    ->delete();

                // Log reversal movement
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'direction' => 'OUT',
                    'quantity' => $item->base_quantity,
                    'source' => 'purchase_void',
                    'source_id' => $purchase->id,
                    'note' => 'Purchase deletion reversal',
                    'moved_at' => now(),
                ]);
            }

            $purchase->delete();
        });

        return redirect()->route('purchases.index')
            ->with('message', 'Pembelian dihapus dan stok dikembalikan.');
    }
}

