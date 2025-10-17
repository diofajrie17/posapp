<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\StockMovement;
use App\Models\InventoryBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct()
{
    $this->middleware('permission:products.view')->only(['index','show']);
    $this->middleware('permission:products.create')->only(['create','store']);
    $this->middleware('permission:products.update')->only(['edit','update']);
    $this->middleware('permission:products.delete')->only(['destroy']);
}

    public function index()
    {
        $products = Product::with(['category', 'unit', 'baseUnit'])->orderBy('name')->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'can' => [
                'create' => auth()->user()->can('products.create'),
                'update' => auth()->user()->can('products.update'),
                'delete' => auth()->user()->can('products.delete'),
            ],
        ]);
    }

    public function create()
    {
        $categories = Category::all();
        // Only show base units for products
        $units = Unit::where('is_base_unit', true)->get();
        
        return Inertia::render('Products/Create', [
            'categories' => $categories,
            'units' => $units,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'unit_id' => 'required|exists:units,id',
            'unit_quantity' => 'nullable|numeric|min:0.0001',
            'base_unit_id' => 'nullable|exists:units,id',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        // Validate that unit_id is a base unit
        $unit = Unit::find($request->unit_id);
        if ($unit && !$unit->is_base_unit) {
            return back()->withErrors(['unit_id' => 'Products can only use base units. Please select a base unit.']);
        }

        $data = $request->only(['name', 'price', 'unit_id', 'unit_quantity', 'base_unit_id', 'category_id']);
        
        // Set defaults for new products
        $data['stock'] = 0;
        $data['cost_price'] = 0;
        
        // Set default unit quantity if not provided
        if (!isset($data['unit_quantity']) || $data['unit_quantity'] <= 0) {
            $data['unit_quantity'] = 1;
        }

        Product::create($data);

        return redirect()->route('products.index')->with('message', 'Produk berhasil ditambahkan');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        // Only show base units for products
        $units = Unit::where('is_base_unit', true)->get();
        
        return Inertia::render('Products/Edit', [
            'product' => $product->load('category', 'unit', 'baseUnit'),
            'categories' => $categories,
            'units' => $units,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'unit_id' => 'required|exists:units,id',
            'unit_quantity' => 'nullable|numeric|min:0.0001',
            'base_unit_id' => 'nullable|exists:units,id',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        // Validate that unit_id is a base unit
        $unit = Unit::find($request->unit_id);
        if ($unit && !$unit->is_base_unit) {
            return back()->withErrors(['unit_id' => 'Products can only use base units. Please select a base unit.']);
        }

        $data = $request->only(['name', 'stock', 'price', 'unit_id', 'unit_quantity', 'base_unit_id', 'category_id']);
        
        // Don't allow manual cost_price updates - it's managed by purchases
        // Keep existing cost_price from database
        
        // Set default unit quantity if not provided
        if (!isset($data['unit_quantity']) || $data['unit_quantity'] <= 0) {
            $data['unit_quantity'] = 1;
        }

        $product->update($data);

        return redirect()->route('products.index')->with('message', 'Produk berhasil diupdate');
    }

    public function destroy(Product $product)
    {
        // Check if product has sales history
        $hasSales = \DB::table('sales_items')->where('product_id', $product->id)->exists();
        
        // Soft delete the product (preserves data integrity)
        $product->delete();

        $message = $hasSales 
            ? 'Produk berhasil dihapus. Data produk masih tersimpan untuk keperluan riwayat penjualan.'
            : 'Produk berhasil dihapus.';

        return redirect()->route('products.index')->with('message', $message);
    }

    /**
     * Stock Opname - Manual stock adjustment
     */
    public function stockOpname(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'actual_quantity' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request) {
            $product = Product::lockForUpdate()->findOrFail($request->product_id);
            
            $systemQuantity = $product->stock;
            $actualQuantity = $request->actual_quantity;
            $difference = $actualQuantity - $systemQuantity;

            if ($difference == 0) {
                return; // No adjustment needed
            }

            // If positive difference, create new batch
            if ($difference > 0) {
                $costToUse = $product->average_cost ?? $product->cost_price ?? 0;
                
                InventoryBatch::create([
                    'product_id' => $product->id,
                    'purchase_id' => null,
                    'quantity_remaining' => $difference,
                    'unit_cost' => $costToUse,
                    'purchased_at' => now(),
                ]);
            } else {
                // If negative difference, consume from oldest batches
                $quantityToRemove = abs($difference);
                $batches = InventoryBatch::getOldestBatches($product->id, $quantityToRemove);
                
                foreach ($batches as $batch) {
                    if ($quantityToRemove <= 0) break;
                    
                    $consumed = min($batch->quantity_remaining, $quantityToRemove);
                    $batch->quantity_remaining -= $consumed;
                    
                    if ($batch->quantity_remaining <= 0) {
                        $batch->delete();
                    } else {
                        $batch->save();
                    }
                    
                    $quantityToRemove -= $consumed;
                }
            }

            // Update product stock
            $product->update(['stock' => $actualQuantity]);

            // Log stock movement
            StockMovement::create([
                'product_id' => $product->id,
                'direction' => $difference > 0 ? 'IN' : 'OUT',
                'quantity' => abs($difference),
                'source' => 'opname',
                'source_id' => null,
                'note' => $request->reason ?? 'Stock opname adjustment',
                'moved_at' => now(),
            ]);
        });

        return redirect()->route('products.index')
            ->with('message', 'Stock opname berhasil disimpan');
    }

    /**
     * Get stock movements for a product
     */
    public function getStockMovements(Product $product)
    {
        $movements = StockMovement::where('product_id', $product->id)
            ->orderByDesc('moved_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return response()->json([
            'product' => $product->load(['unit', 'category']),
            'movements' => $movements,
        ]);
    }

    /**
     * Get available units for a product (for purchase form)
     */
    public function getAvailableUnits(Product $product)
    {
        $units = collect();
        
        // Add product's main unit (should be base unit)
        if ($product->unit) {
            $units->push($product->unit);
        }
        
        // Add derived units that have this product's base unit as parent
        if ($product->unit_id) {
            $derivedUnits = Unit::where('parent_unit_id', $product->unit_id)
                ->where('is_active', true)
                ->get();
            $units = $units->merge($derivedUnits);
        }

        return response()->json($units);
    }

    /**
     * Show all stock movements (from old StockController)
     */
    public function movements(Request $request)
    {
        $query = StockMovement::with('product:id,name,unit')->orderByDesc('moved_at');
        
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        return Inertia::render('Inventory/Movements', [
            'movements' => $query->paginate(25)->withQueryString(),
            'filters'   => $request->only(['product_id','source','direction']),
            'products'  => Product::orderBy('name')->get(['id','name']),
        ]);
    }
}

