<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $r)
    {
        $movements = StockMovement::with(['product', 'user'])
            ->when($r->product_id, fn ($q, $id) => $q->where('product_id', $id))
            ->when($r->type, fn ($q, $t) => $q->where('type', $t))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.stock.index', ['movements' => $movements, 'products' => Product::orderBy('name')->get()]);
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'product_id' => 'required|exists:products,id',
            'jenis' => 'required|in:masuk,koreksi',
            'qty' => 'required|integer|min:0',
            'note' => 'nullable|string|max:200',
        ]);
        $p = Product::findOrFail($d['product_id']);

        if ($d['jenis'] === 'masuk') {
            if ($d['qty'] < 1) {
                return back()->withErrors(['qty' => 'Jumlah stok masuk minimal 1.'])->withInput();
            }
            StockService::move($p, 'masuk', (int) $d['qty'], null, $d['note'] ?: 'Stok masuk manual', auth()->id());

            return back()->with('success', "Stok {$p->name} bertambah {$d['qty']} unit.");
        }

        $delta = (int) $d['qty'] - $p->stock;   // koreksi = set stok fisik hasil stock opname
        if ($delta === 0) {
            return back()->with('info', 'Stok sistem sudah sama dengan stok fisik.');
        }
        StockService::move($p, 'koreksi', $delta, null, $d['note'] ?: 'Koreksi stok opname', auth()->id());

        return back()->with('success', "Stok {$p->name} dikoreksi menjadi {$d['qty']} unit.");
    }
}
