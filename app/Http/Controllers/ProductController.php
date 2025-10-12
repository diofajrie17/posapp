<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Http\Request;
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
        $units = Unit::all();
        $baseUnits = Unit::where('is_base_unit', true)->get();
        
        return Inertia::render('Products/Create', [
            'categories' => $categories,
            'units' => $units,
            'baseUnits' => $baseUnits
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'unit_id' => 'required|exists:units,id',
            'unit_quantity' => 'nullable|numeric|min:0.0001',
            'base_unit_id' => 'nullable|exists:units,id',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        $data = $request->all();
        
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
        $units = Unit::all();
        $baseUnits = Unit::where('is_base_unit', true)->get();
        
        return Inertia::render('Products/Edit', [
            'product' => $product->load('category', 'unit', 'baseUnit'),
            'categories' => $categories,
            'units' => $units,
            'baseUnits' => $baseUnits
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'unit_id' => 'required|exists:units,id',
            'unit_quantity' => 'nullable|numeric|min:0.0001',
            'base_unit_id' => 'nullable|exists:units,id',
            'category_id' => 'nullable|exists:categories,id'
        ]);

        $data = $request->all();
        
        // Set default unit quantity if not provided
        if (!isset($data['unit_quantity']) || $data['unit_quantity'] <= 0) {
            $data['unit_quantity'] = 1;
        }

        $product->update($data);

        return redirect()->route('products.index')->with('message', 'Produk berhasil diupdate');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('message', 'Produk dihapus');
    }
}

