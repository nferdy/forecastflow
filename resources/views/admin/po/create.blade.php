@extends('layouts.admin')
@section('title', 'Buat PO')
@section('content')
<h2 class="page-title mb-4">Buat Purchase Order (manual)</h2>
<form method="GET" class="card-soft p-3 mb-3 d-flex flex-wrap gap-2 align-items-end">
    <div><label class="form-label small mb-1">Pilih supplier</label>
        <select name="supplier_id" class="form-select" onchange="this.form.submit()"><option value="">— pilih —</option>
            @foreach ($suppliers as $s)<option value="{{ $s->id }}" {{ optional($supplier)->id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach</select></div>
</form>
@if ($supplier)
<form method="POST" action="{{ route('admin.po.store') }}">@csrf
<input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
<div class="card-soft p-3">
    <div class="small text-muted mb-2">Jumlah terisi otomatis dari <strong>rekomendasi prediksi</strong>. Ubah sesuai kebutuhan; kosongkan / 0 untuk tidak memesan.</div>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Produk</th><th class="text-end">Stok</th><th class="text-end">ROP</th><th>Status</th><th style="width:130px">Qty pesan</th><th style="width:160px">Harga beli (Rp)</th></tr></thead><tbody>
        @foreach ($rows as $r)
            <tr><td class="fw-semibold">{{ $r->product->name }}</td><td class="text-end">{{ $r->product->stock }}</td><td class="text-end">{{ $r->rop }}</td>
                <td><span class="badge text-bg-{{ \App\Services\ForecastService::STATUS[$r->status][1] }}">{{ \App\Services\ForecastService::STATUS[$r->status][0] }}</span></td>
                <td><input type="number" min="0" name="qty[{{ $r->product->id }}]" class="form-control form-control-sm" value="{{ old('qty.' . $r->product->id, $r->rec) }}"></td>
                <td><input type="number" min="0" name="cost[{{ $r->product->id }}]" class="form-control form-control-sm" value="{{ old('cost.' . $r->product->id, $r->product->cost_price) }}"></td></tr>
        @endforeach
        </tbody></table></div>
    <label class="form-label small">Catatan untuk supplier</label><textarea name="notes" rows="2" class="form-control mb-3">{{ old('notes') }}</textarea>
    <button class="btn btn-brand px-4">Simpan sebagai Draft PO</button>
</div></form>
@endif
@endsection
