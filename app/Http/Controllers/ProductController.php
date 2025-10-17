<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Show product list
     */
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
    public function __construct()
    {
        $this->middleware('permission:products.view')->only(['index','show']);
        $this->middleware('permission:products.create')->only(['create','store']);
        $this->middleware('permission:products.update')->only(['edit','update']);
        $this->middleware('permission:products.delete')->only(['destroy']);
    }

    /**
     * Show form to record newly bought product
     */
    public function purchaseForm()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get(['id','name','stock']);
        return Inertia::render('Products/Purchase', [
            'products' => $products
        ]);
    }

    /**
     * Handle purchase and update product stock
     */
    public function purchaseStore(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'cost_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($request->product_id);
        $product->stock += $request->quantity;
        $product->cost_price = $request->cost_price; // Optionally update cost price
        $product->save();

        // Optionally, record purchase history here

        return redirect()->route('products.index')->with('message', 'Pembelian produk berhasil dicatat dan stok diperbarui.');
    }

}
