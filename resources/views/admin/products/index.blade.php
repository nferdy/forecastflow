@extends('layouts.admin')
@section('title', 'Produk')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <h2 class="page-title mb-0">Produk</h2>
    <a href="{{ route('admin.products.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i> Tambah Produk</a>
</div>
<form class="row g-2 mb-3" method="GET">
    <div class="col-md-5"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama / SKU"></div>
    <div class="col-md-3"><select name="kategori" class="form-select" onchange="this.form.submit()"><option value="">Semua kategori</option>
        @foreach ($categories as $c)<option value="{{ $c->id }}" {{ request('kategori') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach</select></div>
    <div class="col-auto"><button class="btn btn-outline-secondary">Cari</button></div>
</form>
<div class="card-soft p-3"><div class="table-responsive">
<table class="table align-middle mb-0">
    <thead><tr><th>Produk</th><th>Kategori</th><th>Supplier</th><th class="text-end">Harga jual</th><th class="text-end">Harga beli</th><th class="text-end">Stok</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @foreach ($products as $p)
        <tr>
            <td><div class="d-flex align-items-center gap-2"><span class="mini-thumb">@if ($p->image_url)<img src="{{ $p->image_url }}">@else<i class="bi {{ $p->category->icon }}"></i>@endif</span>
                <div><div class="fw-semibold">{{ Str::limit($p->name, 36) }}</div><div class="small text-muted">{{ $p->sku }}</div></div></div></td>
            <td>{{ $p->category->name }}</td><td class="small">{{ $p->supplier->name }}</td>
            <td class="text-end">{{ \App\Support\Fmt::rp($p->price) }}</td><td class="text-end text-muted">{{ \App\Support\Fmt::rp($p->cost_price) }}</td>
            <td class="text-end fw-bold {{ $p->stock <= 0 ? 'text-danger' : '' }}">{{ $p->stock }}</td>
            <td>@if ($p->is_active)<span class="badge bg-success-subtle text-success-emphasis">Aktif</span>@else<span class="badge bg-secondary-subtle text-secondary-emphasis">Nonaktif</span>@endif</td>
            <td class="text-nowrap text-end">
                <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                <form class="d-inline" method="POST" action="{{ route('admin.products.destroy', $p) }}" onsubmit="return confirm('Hapus / nonaktifkan produk ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table></div></div>
<div class="mt-3">{{ $products->links() }}</div>
@endsection
