<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:products.view')->only(['index', 'show']);
        $this->middleware('permission:products.create')->only(['create', 'store']);
        $this->middleware('permission:products.update')->only(['edit', 'update']);
        $this->middleware('permission:products.delete')->only(['destroy']);
    }

    public function index()
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'can' => [
                'create' => auth()->user()->can('products.create'),
                'update' => auth()->user()->can('products.update'),
                'delete' => auth()->user()->can('products.delete'),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Categories/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create($request->all());

        return redirect()->route('categories.index')->with('message', 'Kategori berhasil ditambahkan');
    }

    public function show(Category $category)
    {
        $category->load('products');
        return Inertia::render('Categories/Show', [
            'category' => $category
        ]);
    }

    public function edit(Category $category)
    {
        return Inertia::render('Categories/Edit', [
            'category' => $category
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update($request->all());

        return redirect()->route('categories.index')->with('message', 'Kategori berhasil diupdate');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('categories.index')->with('message', 'Kategori berhasil dihapus');
    }
}
