<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Services\ForecastService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(ForecastService $f)
    {
        $rows = $f->analyze();
        $alerts = $rows->filter(fn ($r) => $r->alert)
            ->sortBy(fn ($r) => $r->rop > 0 ? $r->product->stock / $r->rop : 0)->values();

        $since = now()->subDays(29)->startOfDay();

        $stats = [
            'products' => Product::count(),
            'alerts' => $alerts->count(),
            'pending' => Order::where('status', 'menunggu_verifikasi')->count(),
            'revenue' => Order::whereIn('status', ['diproses', 'dikirim', 'selesai'])->where('created_at', '>=', $since)->sum('total'),
            'open_po' => PurchaseOrder::whereIn('status', ['draft', 'terkirim'])->count(),
            'overstock' => $rows->where('status', 'overstock')->count(),
        ];

        $series = $f->series(null, 30);

        $top = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', '!=', 'dibatalkan')
            ->where('orders.created_at', '>=', $since)
            ->selectRaw('products.name as name, SUM(order_items.qty) as qty')
            ->groupBy('products.name')->orderByDesc('qty')->limit(6)->get();

        $cmp = $rows->sortBy(fn ($r) => $r->rop > 0 ? $r->product->stock / $r->rop : 99)->take(8)->values();
        $stockChart = [
            'labels' => $cmp->map(fn ($r) => Str::limit($r->product->name, 18))->all(),
            'stock' => $cmp->map(fn ($r) => $r->product->stock)->all(),
            'rop' => $cmp->map(fn ($r) => $r->rop)->all(),
        ];

        $recentOrders = Order::with('user')->latest()->limit(6)->get();

        return view('admin.dashboard', compact('stats', 'alerts', 'series', 'top', 'stockChart', 'recentOrders') + ['settings' => $f->settings()]);
    }
}
