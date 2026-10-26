<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PurchaseOrderItem;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Modul prediksi stok ForecastFlow.
 *
 *  Moving Average (rata-rata permintaan harian) = (D1 + D2 + ... + Dn) / n
 *  Safety Stock = rata-rata permintaan harian x jumlah hari cadangan
 *  ROP          = (rata-rata permintaan harian x lead time supplier) + Safety Stock
 */
class ForecastService
{
    public const STATUS = [
        'habis' => ['Stok Habis', 'danger'],
        'kritis' => ['Kritis (<= Safety Stock)', 'danger'],
        'reorder' => ['Perlu Restock (<= ROP)', 'warning'],
        'aman' => ['Aman', 'success'],
        'overstock' => ['Overstock', 'info'],
        'slow' => ['Slow Moving', 'secondary'],
    ];

    public function settings(): array
    {
        return [
            'n' => max(1, (int) Setting::get('ma_period', 7)),    // periode Moving Average (hari)
            'safety_days' => max(0, (int) Setting::get('safety_days', 3)),  // hari cadangan safety stock
            'cover_days' => max(1, (int) Setting::get('cover_days', 14)),  // target cakupan stok setelah restock
        ];
    }

    protected function salesBase()
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'dibatalkan');
    }

    /** Penjualan per hari (zero-filled) untuk $days hari terakhir. [Y-m-d => qty] */
    public function dailySales(?int $productId, int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $q = $this->salesBase()
            ->where('orders.created_at', '>=', $start)
            ->selectRaw('DATE(orders.created_at) as d, SUM(order_items.qty) as qty')
            ->groupBy('d');

        if ($productId) {
            $q->where('order_items.product_id', $productId);
        }

        $rows = $q->pluck('qty', 'd');
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $out[$date] = (int) ($rows[$date] ?? 0);
        }

        return $out;
    }

    /** Deret untuk grafik: penjualan harian + garis Moving Average (jendela n hari). */
    public function series(?int $productId, int $days = 30): array
    {
        $n = $this->settings()['n'];
        $raw = $this->dailySales($productId, $days + $n - 1);
        $dates = array_keys($raw);
        $vals = array_values($raw);

        $labels = $sales = $ma = [];
        for ($i = $n - 1; $i < count($vals); $i++) {
            $labels[] = Carbon::parse($dates[$i])->translatedFormat('d M');
            $sales[] = $vals[$i];
            $ma[] = round(array_sum(array_slice($vals, $i - $n + 1, $n)) / $n, 2);
        }

        return compact('labels', 'sales', 'ma');
    }

    /** Analisis seluruh produk aktif: avg, safety stock, ROP, rekomendasi & status. */
    public function analyze(): Collection
    {
        $s = $this->settings();
        $since = now()->subDays($s['n'] - 1)->startOfDay();

        $sold = $this->salesBase()
            ->where('orders.created_at', '>=', $since)
            ->selectRaw('order_items.product_id as pid, SUM(order_items.qty) as qty')
            ->groupBy('order_items.product_id')
            ->pluck('qty', 'pid');

        // barang yang sudah dipesan lewat PO (draft/terkirim) tapi belum diterima
        $incoming = PurchaseOrderItem::query()
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->whereIn('purchase_orders.status', ['draft', 'terkirim'])
            ->selectRaw('purchase_order_items.product_id as pid, SUM(purchase_order_items.qty) as qty')
            ->groupBy('purchase_order_items.product_id')
            ->pluck('qty', 'pid');

        return Product::with(['supplier', 'category'])->active()->orderBy('name')->get()
            ->map(function ($p) use ($s, $sold, $incoming) {
                $avg = ((int) ($sold[$p->id] ?? 0)) / $s['n'];
                $lead = $p->supplier->lead_time_days ?? 5;
                $ss = (int) ceil($avg * $s['safety_days']);
                $rop = (int) ceil($avg * $lead + $ss);
                $target = $rop + (int) ceil($avg * $s['cover_days']);
                $inc = (int) ($incoming[$p->id] ?? 0);
                $rec = max(0, $target - $p->stock - $inc);

                if ($p->stock <= 0) {
                    $status = 'habis';
                } elseif ($avg > 0 && $p->stock <= $ss) {
                    $status = 'kritis';
                } elseif ($avg > 0 && $p->stock <= $rop) {
                    $status = 'reorder';
                } elseif ($avg == 0) {
                    $status = 'slow';
                } elseif ($p->stock > $target * 2) {
                    $status = 'overstock';
                } else {
                    $status = 'aman';
                }

                return (object) [
                    'product' => $p,
                    'avg' => round($avg, 2),
                    'lead' => $lead,
                    'ss' => $ss,
                    'rop' => $rop,
                    'target' => $target,
                    'incoming' => $inc,
                    'rec' => $rec,
                    'status' => $status,
                    'alert' => in_array($status, ['habis', 'kritis', 'reorder']),
                    'daily_cover' => $avg > 0 ? round($p->stock / $avg, 1) : null,
                ];
            });
    }
}
