<?php

namespace App\Http\Controllers;

use App\Models\Product;
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
$products = Product::orderBy('name')->get();

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
        return Inertia::render('Products/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'stock' => 'required|integer',
            'price' => 'required|numeric',
            'unit' => 'required'
        ]);

        Product::create($request->all());

        return redirect()->route('products.index')->with('message', 'Produk ditambahkan');
    }

    public function edit(Product $product)
    {
        return Inertia::render('Products/Edit', [
            'product' => $product
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($request->all());

        return redirect()->route('products.index')->with('message', 'Produk diupdate');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('message', 'Produk dihapus');
    }
}

