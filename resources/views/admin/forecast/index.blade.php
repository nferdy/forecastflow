@extends('layouts.admin')
@section('title', 'Prediksi Stok')
@section('content')
@php $st = \App\Services\ForecastService::STATUS; @endphp
<div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-2">
    <div><h2 class="page-title mb-0">Prediksi Stok</h2><div class="text-muted">Moving Average · Safety Stock · Reorder Point (ROP)</div></div>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('admin.po.auto') }}">@csrf
            <button class="btn btn-brand" {{ $summary['alert'] ? '' : 'disabled' }}><i class="bi bi-lightning-charge-fill me-1"></i>Auto-PO (draft)</button></form>
        <form method="POST" action="{{ route('admin.po.auto') }}" onsubmit="return confirm('Buat PO & langsung kirim email ke supplier?')">@csrf<input type="hidden" name="kirim" value="1">
            <button class="btn btn-outline-brand" {{ $summary['alert'] ? '' : 'disabled' }}><i class="bi bi-send me-1"></i>Auto-PO + Kirim Email</button></form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-5">
        <div class="card-soft p-4 h-100">
            <h6 class="fw-bold mb-3"><i class="bi bi-sliders me-1"></i> Parameter prediksi</h6>
            <form method="POST" action="{{ route('admin.forecast.settings') }}">@csrf
                <div class="row g-2">
                    <div class="col-4"><label class="form-label small mb-1">Periode MA (n hari)</label><input type="number" name="ma_period" class="form-control" value="{{ $settings['n'] }}" min="1" max="60"></div>
                    <div class="col-4"><label class="form-label small mb-1">Hari Safety Stock</label><input type="number" name="safety_days" class="form-control" value="{{ $settings['safety_days'] }}" min="0" max="30"></div>
                    <div class="col-4"><label class="form-label small mb-1">Cakupan restock (hari)</label><input type="number" name="cover_days" class="form-control" value="{{ $settings['cover_days'] }}" min="1" max="90"></div>
                </div>
                <button class="btn btn-sm btn-brand mt-3">Simpan &amp; hitung ulang</button>
            </form>
            <div class="small text-muted mt-3"><i class="bi bi-info-circle"></i> Moving Average kurang peka terhadap musiman/lonjakan mendadak — sesuaikan n dan safety stock bila perlu.</div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="formula h-100">
            <div class="text-secondary mb-1">// Rumus yang dipakai (sesuai proposal)</div>
            <div><b>Moving Average</b> = (D₁ + D₂ + … + Dₙ) / n</div>
            <div><b>Safety Stock</b>&nbsp; = rata-rata harian × hari cadangan ({{ $settings['safety_days'] }})</div>
            <div><b>ROP</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;= (rata-rata harian × lead time) + Safety Stock</div>
            <div><b>Rekomendasi PO</b> = ROP + (rata² × {{ $settings['cover_days'] }} hari) − stok − PO berjalan</div>
            <div class="text-secondary mt-1">// stok ≤ ROP → notifikasi muncul agar admin membuat PO</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    @foreach ([['Perlu tindakan', $summary['alert'], 'danger'], ['Aman', $summary['aman'], 'success'], ['Overstock', $summary['overstock'], 'info'], ['Slow moving', $summary['slow'], 'secondary']] as [$l, $v, $c])
        <div class="col-6 col-md-3"><div class="stat-card py-3"><div class="text-muted small">{{ $l }}</div><div class="stat-val text-{{ $c }}">{{ $v }}</div></div></div>
    @endforeach
</div>

<div class="card-soft p-3">
<div class="table-responsive">
<table class="table align-middle mb-0">
    <thead><tr><th>Produk</th><th>Supplier (lead)</th><th class="text-end">Stok</th><th class="text-end">Rata²/hari (MA)</th><th class="text-end">Safety</th><th class="text-end">ROP</th><th class="text-end">Cakupan</th><th class="text-end">PO berjalan</th><th class="text-end">Saran PO</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @foreach ($rows->sortByDesc('alert') as $r)
        <tr class="{{ $r->alert ? 'table-danger bg-opacity-25' : '' }}">
            <td class="fw-semibold">{{ Str::limit($r->product->name, 34) }}<div class="small text-muted fw-normal">{{ $r->product->category->name }}</div></td>
            <td class="small">{{ $r->product->supplier->name }}<div class="text-muted">{{ $r->lead }} hari</div></td>
            <td class="text-end fw-bold">{{ $r->product->stock }}</td>
            <td class="text-end">{{ rtrim(rtrim(number_format($r->avg, 2, ',', '.'), '0'), ',') }}</td>
            <td class="text-end">{{ $r->ss }}</td>
            <td class="text-end fw-bold text-warning-emphasis">{{ $r->rop }}</td>
            <td class="text-end small">{{ $r->daily_cover !== null ? $r->daily_cover . ' hr' : '—' }}</td>
            <td class="text-end">{{ $r->incoming ?: '—' }}</td>
            <td class="text-end fw-bold">{{ $r->rec ?: '—' }}</td>
            <td><span class="badge text-bg-{{ $st[$r->status][1] }}">{{ $st[$r->status][0] }}</span></td>
            <td><a href="{{ route('admin.forecast.show', $r->product) }}" class="btn btn-sm btn-outline-brand">Detail</a></td>
        </tr>
    @endforeach
    </tbody>
</table>
</div></div>
@endsection
