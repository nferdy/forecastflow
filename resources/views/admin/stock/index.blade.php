@extends('layouts.admin')
@section('title', 'Stok & Mutasi')
@section('content')
<h2 class="page-title mb-4">Stok &amp; Riwayat Mutasi</h2>
<div class="row g-3">
    <div class="col-xl-4">
        <div class="card-soft p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-box-arrow-in-down me-1 text-brand"></i> Catat stok masuk / koreksi</h6>
            <form method="POST" action="{{ route('admin.stock.store') }}">@csrf
                <div class="mb-2"><label class="form-label small">Produk</label>
                    <select name="product_id" class="form-select">@foreach ($products as $p)<option value="{{ $p->id }}">{{ $p->name }} (stok {{ $p->stock }})</option>@endforeach</select></div>
                <div class="mb-2"><label class="form-label small">Jenis</label>
                    <select name="jenis" class="form-select"><option value="masuk">Stok masuk (+ tambah)</option><option value="koreksi">Koreksi stok opname (set stok fisik)</option></select></div>
                <div class="mb-2"><label class="form-label small">Jumlah</label><input type="number" name="qty" class="form-control" min="0" value="{{ old('qty') }}" required></div>
                <div class="mb-3"><label class="form-label small">Catatan</label><input name="note" class="form-control" placeholder="mis. barang dari supplier / selisih opname"></div>
                <button class="btn btn-brand w-100">Simpan</button>
            </form>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card-soft p-3">
            <form class="row g-2 mb-3" method="GET">
                <div class="col-md-6"><select name="product_id" class="form-select form-select-sm" onchange="this.form.submit()"><option value="">Semua produk</option>@foreach ($products as $p)<option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>@endforeach</select></div>
                <div class="col-md-4"><select name="type" class="form-select form-select-sm" onchange="this.form.submit()"><option value="">Semua jenis</option>@foreach (['masuk', 'keluar', 'koreksi'] as $t)<option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>@endforeach</select></div>
            </form>
            <div class="table-responsive"><table class="table table-sm align-middle mb-0">
                <thead><tr><th>Waktu</th><th>Produk</th><th>Jenis</th><th class="text-end">Qty</th><th class="text-end">Stok akhir</th><th>Referensi / catatan</th></tr></thead><tbody>
                @forelse ($movements as $m)
                    <tr><td class="small text-muted">{{ $m->created_at->translatedFormat('d M Y H:i') }}</td><td>{{ Str::limit($m->product->name, 28) }}</td>
                        <td><span class="badge text-bg-{{ ['masuk' => 'success', 'keluar' => 'danger', 'koreksi' => 'warning'][$m->type] ?? 'secondary' }}">{{ ucfirst($m->type) }}</span></td>
                        <td class="text-end fw-bold {{ $m->qty < 0 ? 'text-danger' : 'text-success' }}">{{ $m->qty > 0 ? '+' : '' }}{{ $m->qty }}</td>
                        <td class="text-end">{{ $m->stock_after }}</td><td class="small">{{ $m->reference }} {{ $m->note }}</td></tr>
                @empty<tr><td colspan="6" class="text-center text-muted py-4">Belum ada mutasi.</td></tr>@endforelse
                </tbody></table></div>
            <div class="mt-3">{{ $movements->links() }}</div>
        </div>
    </div>
</div>
@endsection
