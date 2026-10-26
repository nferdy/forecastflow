@extends('layouts.admin')
@section('title', 'Detail Prediksi')
@section('content')
@php $st = \App\Services\ForecastService::STATUS; $sum = array_sum($window); $n = $settings['n']; @endphp
<a href="{{ route('admin.forecast.index') }}" class="small">← Kembali ke prediksi</a>
<div class="d-flex flex-wrap justify-content-between align-items-center my-3">
    <div><h2 class="page-title mb-0">{{ $product->name }}</h2>
        <div class="text-muted">{{ $product->category->name }} · SKU {{ $product->sku }} · {{ $product->supplier->name }} (lead time {{ $row->lead }} hari)</div></div>
    <span class="badge text-bg-{{ $st[$row->status][1] }} fs-6">{{ $st[$row->status][0] }}</span>
</div>

<div class="row g-3 mb-4">
    @foreach ([['Stok saat ini', $product->stock, 'bi-box-seam', 'primary'], ['Rata² permintaan/hari', $row->avg, 'bi-graph-up', 'info'], ['Reorder Point', $row->rop, 'bi-flag', 'warning'], ['Rekomendasi PO', $row->rec . ' unit', 'bi-cart-plus', 'success']] as [$l, $v, $i, $c])
        <div class="col-6 col-xl-3"><div class="stat-card d-flex gap-3 align-items-center"><div class="stat-icon bg-{{ $c }}-subtle text-{{ $c }}"><i class="bi {{ $i }}"></i></div><div><div class="small text-muted">{{ $l }}</div><div class="stat-val">{{ $v }}</div></div></div></div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-xl-7">
        <div class="card-soft p-4 h-100">
            <h6 class="fw-bold">Penjualan harian vs Moving Average ({{ $n }} hari)</h6>
            <div class="chart-box"><canvas id="c"></canvas></div>
        </div>
    </div>
    <div class="col-xl-5">
        <div class="card-soft p-4 h-100">
            <h6 class="fw-bold">Hitungan transparan (bisa dicek manual)</h6>
            <div class="table-responsive" style="max-height:200px">
                <table class="table table-sm mb-2"><thead><tr><th>Tanggal</th><th class="text-end">Terjual (D)</th></tr></thead><tbody>
                @foreach ($window as $d => $q)<tr><td>{{ \Carbon\Carbon::parse($d)->translatedFormat('D, d M') }}</td><td class="text-end">{{ $q }}</td></tr>@endforeach
                </tbody></table>
            </div>
            <div class="formula">
                <div>MA = {{ $sum }} / {{ $n }} = <b>{{ $row->avg }}</b> unit/hari</div>
                <div>Safety Stock = {{ $row->avg }} × {{ $settings['safety_days'] }} = <b>{{ $row->ss }}</b></div>
                <div>ROP = ({{ $row->avg }} × {{ $row->lead }}) + {{ $row->ss }} = <b>{{ $row->rop }}</b></div>
                <div>Target = {{ $row->rop }} + ({{ $row->avg }} × {{ $settings['cover_days'] }}) = <b>{{ $row->target }}</b></div>
                <div>Saran PO = {{ $row->target }} − {{ $product->stock }} − {{ $row->incoming }} = <b>{{ $row->rec }}</b></div>
            </div>
            <div class="small text-muted mt-2">Prediksi kebutuhan 7 hari ke depan ≈ <strong>{{ round($row->avg * 7) }} unit</strong>.</div>
            @if ($row->rec > 0 && $row->alert)
                <a href="{{ route('admin.po.create', ['supplier_id' => $product->supplier_id]) }}" class="btn btn-brand w-100 mt-3"><i class="bi bi-file-earmark-plus me-1"></i> Buat PO ke {{ $product->supplier->name }}</a>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const s = @json($series);
    const rop = {{ $row->rop }};
    new Chart(document.getElementById('c'), { data: { labels: s.labels, datasets: [
      { type: 'bar', label: 'Terjual', data: s.sales, backgroundColor: 'rgba(99,102,241,.4)', borderRadius: 5 },
      { type: 'line', label: 'Moving Average', data: s.ma, borderColor: '#06b6d4', tension: .35, borderWidth: 3, pointRadius: 0 } ]},
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } } });
})();
</script>
@endpush
