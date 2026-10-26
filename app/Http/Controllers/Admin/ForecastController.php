<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use App\Services\ForecastService;
use Illuminate\Http\Request;

class ForecastController extends Controller
{
    public function index(ForecastService $f)
    {
        $rows = $f->analyze();
        $summary = [
            'alert' => $rows->where('alert', true)->count(),
            'aman' => $rows->where('status', 'aman')->count(),
            'overstock' => $rows->where('status', 'overstock')->count(),
            'slow' => $rows->where('status', 'slow')->count(),
        ];

        return view('admin.forecast.index', ['rows' => $rows, 'settings' => $f->settings(), 'summary' => $summary]);
    }

    public function show(Product $product, ForecastService $f)
    {
        $product->load(['supplier', 'category']);
        $s = $f->settings();
        $row = $f->analyze()->firstWhere('product.id', $product->id);
        $window = $f->dailySales($product->id, $s['n']);   // data n hari terakhir (untuk hitungan manual)
        $series = $f->series($product->id, 30);

        return view('admin.forecast.show', compact('product', 'row', 'window', 'series') + ['settings' => $s]);
    }

    public function settings(Request $r)
    {
        $d = $r->validate([
            'ma_period' => 'required|integer|min:1|max:60',
            'safety_days' => 'required|integer|min:0|max:30',
            'cover_days' => 'required|integer|min:1|max:90',
        ]);
        foreach ($d as $k => $v) {
            Setting::put($k, $v);
        }

        return back()->with('success', 'Parameter prediksi diperbarui. Seluruh hasil dihitung ulang.');
    }
}
