@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
@php $st = \App\Services\ForecastService::STATUS; @endphp
<div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
    <div>
        <h2 class="page-title mb-0">Dashboard Analitik</h2>
        <div class="text-muted">Ringkasan penjualan &amp; kesehatan stok · Moving Average {{ $settings['n'] }} hari</div>
    </div>
    <form method="POST" action="{{ route('admin.po.auto') }}" class="d-flex gap-2 mt-3 mt-md-0">@csrf
        <button class="btn btn-brand" {{ $stats['alerts'] ? '' : 'disabled' }}><i class="bi bi-lightning-charge-fill me-1"></i> Buat Auto-PO</button>
    </form>
</div>

<div class="row g-3 mb-4">
    @foreach ([
        ['Produk aktif', $stats['products'], 'bi-box-seam', 'primary'],
        ['Perlu restock', $stats['alerts'], 'bi-bell-fill', 'danger'],
        ['Menunggu verifikasi', $stats['pending'], 'bi-hourglass-split', 'warning'],
        ['Omzet 30 hari', \App\Support\Fmt::rp($stats['revenue']), 'bi-cash-stack', 'success'],
    ] as [$label, $val, $icon, $color])
        <div class="col-6 col-xl-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon bg-{{ $color }}-subtle text-{{ $color }}"><i class="bi {{ $icon }}"></i></div>
                <div><div class="text-muted small">{{ $label }}</div><div class="stat-val" style="{{ strlen((string) $val) > 9 ? 'font-size:1.15rem' : '' }}">{{ $val }}</div></div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card-soft p-4 h-100">
            <div class="d-flex justify-content-between"><h6 class="fw-bold">Tren Penjualan Harian (30 hari)</h6><span class="badge bg-primary-subtle text-primary-emphasis align-self-start">Garis = Moving Average {{ $settings['n'] }} hari</span></div>
            <div class="chart-box"><canvas id="trend"></canvas></div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card-soft p-4 h-100">
            <h6 class="fw-bold">Produk Terlaris (30 hari)</h6>
            <div class="chart-box"><canvas id="top"></canvas></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-5">
        <div class="card-soft p-4 h-100">
            <h6 class="fw-bold">Stok vs Reorder Point (paling genting)</h6>
            <div class="chart-box"><canvas id="rop"></canvas></div>
        </div>
    </div>
    <div class="col-xl-7">
        <div class="card-soft p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0"><i class="bi bi-bell-fill text-danger me-1"></i> Notifikasi Stok</h6>
                <a href="{{ route('admin.forecast.index') }}" class="small">Lihat semua prediksi →</a>
            </div>
            @if ($alerts->isEmpty())
                <div class="text-center text-muted py-5"><i class="bi bi-check2-circle fs-1 text-success"></i><p class="mb-0">Semua stok aman di atas ROP.</p></div>
            @else
            <div class="table-responsive" style="max-height:310px">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>Produk</th><th>Stok</th><th>ROP</th><th>Saran PO</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($alerts as $a)
                    <tr>
                        <td><a href="{{ route('admin.forecast.show', $a->product) }}" class="fw-semibold text-dark text-decoration-none">{{ Str::limit($a->product->name, 30) }}</a></td>
                        <td class="fw-bold text-{{ $st[$a->status][1] }}">{{ $a->product->stock }}</td>
                        <td>{{ $a->rop }}</td>
                        <td>{{ $a->incoming ? 'PO berjalan' : $a->rec . ' unit' }}</td>
                        <td><span class="badge text-bg-{{ $st[$a->status][1] }}">{{ $a->status === 'reorder' ? 'Restock' : ucfirst($a->status) }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="card-soft p-4">
    <div class="d-flex justify-content-between mb-2"><h6 class="fw-bold">Pesanan Terbaru</h6><a href="{{ route('admin.orders.index') }}" class="small">Semua pesanan →</a></div>
    <table class="table table-sm align-middle mb-0">
        <thead><tr><th>No.</th><th>Pelanggan</th><th>Total</th><th>Status</th><th>Waktu</th></tr></thead>
        <tbody>
        @foreach ($recentOrders as $o)
            <tr><td><a href="{{ route('admin.orders.show', $o) }}">{{ $o->order_number }}</a></td><td>{{ $o->user->name }}</td>
                <td>{{ \App\Support\Fmt::rp($o->total) }}</td><td><span class="badge text-bg-{{ $o->status_color }}">{{ $o->status_label }}</span></td>
                <td class="text-muted small">{{ $o->created_at->diffForHumans() }}</td></tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const trend = @json($series);
    const topData = @json($top);
    const rop = @json($stockChart);
    Chart.defaults.font.family = 'Inter, sans-serif';
    new Chart(document.getElementById('trend'), {
      data: { labels: trend.labels, datasets: [
        { type: 'bar', label: 'Terjual / hari', data: trend.sales, backgroundColor: 'rgba(99,102,241,.35)', borderRadius: 6 },
        { type: 'line', label: 'Moving Average', data: trend.ma, borderColor: '#06b6d4', backgroundColor: '#06b6d4', tension: .35, borderWidth: 3, pointRadius: 0 }
      ]}, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
    });
    new Chart(document.getElementById('top'), {
      type: 'bar', data: { labels: topData.map(t => t.name.length > 20 ? t.name.slice(0, 20) + '…' : t.name),
      datasets: [{ label: 'Unit terjual', data: topData.map(t => Number(t.qty)), backgroundColor: ['#4f46e5','#6366f1','#7c3aed','#8b5cf6','#06b6d4','#22d3ee'], borderRadius: 6 }] },
      options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });
    new Chart(document.getElementById('rop'), {
      type: 'bar', data: { labels: rop.labels, datasets: [
        { label: 'Stok', data: rop.stock, backgroundColor: '#4f46e5', borderRadius: 6 },
        { label: 'ROP', data: rop.rop, backgroundColor: '#f97316', borderRadius: 6 } ]},
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { x: { ticks: { maxRotation: 60, minRotation: 40 } } } }
    });
})();
</script>
@endpush
