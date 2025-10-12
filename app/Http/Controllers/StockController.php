<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->get(['id','name','stock','unit','price','cost_price']);
        return Inertia::render('Inventory/StockIndex', [
            'products' => $products,
        ]);
    }

    public function create()
    {
        // halaman opname sederhana: pilih produk + input qty_actual
        $products = Product::orderBy('name')->get(['id','name','stock','unit']);
        return Inertia::render('Inventory/StockOpname', ['products' => $products]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty_actual' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:200',
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['items'] as $it) {
                $p = Product::lockForUpdate()->find($it['product_id']); // lock biar aman
                $qtySystem = $p->stock;
                $qtyActual = (int)$it['qty_actual'];
                $diff = $qtyActual - $qtySystem;

                if ($diff === 0) continue; // tidak perlu adjustment

                // 1) catat adjustment
                $adj = StockAdjustment::create([
                    'product_id' => $p->id,
                    'qty_system' => $qtySystem,
                    'qty_actual' => $qtyActual,
                    'difference' => $diff,
                    'reason'     => request('reason'),
                    'user_id'    => auth()->id(),
                    'adjusted_at'=> now(),
                ]);

                // 2) update stok product
                $p->update(['stock' => $qtyActual]);

                // 3) log movement
                StockMovement::create([
                    'product_id' => $p->id,
                    'direction'  => $diff > 0 ? 'IN' : 'OUT',
                    'quantity'   => abs($diff),
                    'source'     => 'opname',
                    'source_id'  => $adj->id,
                    'note'       => 'Stock opname',
                    'moved_at'   => now(),
                ]);
            }
        });

        return redirect()->route('stock.index')->with('message','Stock opname tersimpan.');
    }

    public function movements(Request $r)
    {
        $q = StockMovement::with('product:id,name,unit')->orderByDesc('moved_at');
        if ($r->filled('product_id')) $q->where('product_id', $r->product_id);
        if ($r->filled('source')) $q->where('source', $r->source);
        if ($r->filled('direction')) $q->where('direction', $r->direction);

        return Inertia::render('Inventory/Movements', [
            'movements' => $q->paginate(25)->withQueryString(),
            'filters'   => $r->only(['product_id','source','direction']),
            'products'  => Product::orderBy('name')->get(['id','name']),
        ]);
    }
}
