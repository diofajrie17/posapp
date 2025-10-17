<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StockAdjustmentController extends Controller
{
    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get(['id','name','stock']);
        return Inertia::render('Inventory/StockAdjustment', [
            'products' => $products
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty_actual' => 'required|numeric|min:0',
            'reason'     => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($request->product_id);
        $qty_system = $product->stock;
        $qty_actual = $request->qty_actual;
        $difference = $qty_actual - $qty_system;

        // Create adjustment record
        $adjustment = StockAdjustment::create([
            'product_id' => $product->id,
            'qty_system' => $qty_system,
            'qty_actual' => $qty_actual,
            'difference' => $difference,
            'reason'     => $request->reason,
            'user_id'    => Auth::id(),
            'adjusted_at'=> now(),
        ]);

        // Update product stock
        $product->stock = $qty_actual;
        $product->save();

        return redirect()->route('stock-adjustments.create')->with('message', 'Stock adjusted successfully!');
    }
}
