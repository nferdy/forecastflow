@extends('layouts.shop')
@section('title', 'Katalog Sparepart PC')
@section('content')
<div class="hero p-4 p-md-5 mb-4">
    <div class="row align-items-center">
        <div class="col-lg-7">
            <span class="badge rounded-pill text-bg-light bg-opacity-25 mb-3">Vortex Supply Dynamics · VSD Lab</span>
            <h1 class="fw-bold display-6 mb-3">Sparepart PC lengkap, <br>stok transparan &amp; real-time.</h1>
            <p class="opacity-75 mb-4">RAM, motherboard, SSD/HDD, kartu grafis, dan periferal untuk perakitan, upgrade, laboratorium, maupun usaha ritel teknologi. Melayani pelanggan perorangan (B2C) dan mitra toko (B2B).</p>
            <div class="d-flex flex-wrap gap-3 small">
                <span><i class="bi bi-check-circle-fill text-info"></i> Stok akurat</span>
                <span><i class="bi bi-check-circle-fill text-info"></i> Checkout mudah</span>
                <span><i class="bi bi-check-circle-fill text-info"></i> Pelacakan resi</span>
            </div>
        </div>
        <div class="col-lg-5 d-none d-lg-block text-center"><i class="bi bi-motherboard" style="font-size:9rem;opacity:.28"></i></div>
    </div>
</div>

<div class="d-flex gap-2 overflow-auto pb-2 mb-3">
    <a class="cat-pill {{ request('kategori') ? '' : 'active' }}" href="{{ route('shop.index') }}"><i class="bi bi-grid"></i> Semua</a>
    @foreach ($categories as $c)
        <a class="cat-pill {{ request('kategori') === $c->slug ? 'active' : '' }}" href="{{ route('shop.index', ['kategori' => $c->slug]) }}">
            <i class="bi {{ $c->icon }}"></i> {{ $c->name }} <small class="opacity-75">({{ $c->products_count }})</small>
        </a>
    @endforeach
</div>

<form class="d-flex flex-wrap align-items-center gap-3 mb-4" method="GET">
    @foreach (['q', 'kategori'] as $k) @if (request($k))<input type="hidden" name="{{ $k }}" value="{{ request($k) }}">@endif @endforeach
    <div class="text-muted small">{{ $products->total() }} produk ditemukan</div>
    <div class="form-check ms-auto mb-0">
        <input class="form-check-input" type="checkbox" name="tersedia" value="1" id="tersedia" {{ request('tersedia') ? 'checked' : '' }} onchange="this.form.submit()">
        <label class="form-check-label small" for="tersedia">Hanya yang tersedia</label>
    </div>
    <select name="urut" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
        <option value="">Urutkan: Nama</option>
        <option value="murah" {{ request('urut') === 'murah' ? 'selected' : '' }}>Harga terendah</option>
        <option value="mahal" {{ request('urut') === 'mahal' ? 'selected' : '' }}>Harga tertinggi</option>
        <option value="terbaru" {{ request('urut') === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
    </select>
</form>

<div class="row g-3">
    @forelse ($products as $p)
        <div class="col-6 col-md-4 col-xl-3">@include('shop._card', ['p' => $p])</div>
    @empty
        <div class="col-12 text-center text-muted py-5"><i class="bi bi-emoji-frown fs-1"></i><p class="mt-2">Produk tidak ditemukan.</p></div>
    @endforelse
</div>
<div class="mt-4">{{ $products->links() }}</div>
@endsection
