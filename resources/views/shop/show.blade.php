@extends('layouts.shop')
@section('title', $product->name)
@section('content')
<nav class="small mb-3"><a href="{{ route('shop.index') }}">Katalog</a> / <a href="{{ route('shop.index', ['kategori' => $product->category->slug]) }}">{{ $product->category->name }}</a> / <span class="text-muted">{{ $product->name }}</span></nav>
<div class="row g-4">
    <div class="col-lg-6">
        <div class="thumb thumb-lg shadow-sm">
            @if ($product->image_url)<img src="{{ $product->image_url }}" alt="">@else<i class="bi {{ $product->category->icon }}"></i>@endif
        </div>
    </div>
    <div class="col-lg-6">
        <span class="badge bg-primary-subtle text-primary-emphasis mb-2">{{ $product->category->name }}</span>
        <h2 class="fw-bold">{{ $product->name }}</h2>
        <div class="text-muted small mb-3">SKU: {{ $product->sku }}</div>
        <div class="display-6 fw-bold text-brand mb-3">{{ \App\Support\Fmt::rp($product->price) }}</div>

        <div class="card-soft p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-box-seam me-1"></i>Ketersediaan stok</span>
                @if ($product->stock <= 0)<span class="badge text-bg-danger fs-6">Habis</span>
                @elseif ($product->stock <= 5)<span class="badge text-bg-warning fs-6">Tersisa {{ $product->stock }} unit</span>
                @else<span class="badge text-bg-success fs-6">Tersedia · {{ $product->stock }} unit</span>@endif
            </div>
        </div>

        <p class="text-muted">{{ $product->description ?: 'Sparepart PC original dengan kualitas terjamin dari VSD Lab.' }}</p>

        @if ($product->stock > 0)
            <form method="POST" action="{{ route('cart.add', $product) }}" class="d-flex gap-2 mb-3">@csrf
                <input type="number" name="qty" value="1" min="1" max="{{ $product->stock }}" class="form-control" style="max-width:110px">
                <button class="btn btn-brand px-4"><i class="bi bi-cart-plus me-1"></i> Tambah ke Keranjang</button>
            </form>
        @else
            <button class="btn btn-secondary" disabled>Stok sedang habis</button>
        @endif
        <div class="small text-muted"><i class="bi bi-shield-check text-success"></i> Garansi toko · <i class="bi bi-truck text-primary"></i> Dikirim via ekspedisi</div>
    </div>
</div>

@if ($related->count())
    <h5 class="fw-bold mt-5 mb-3">Produk terkait</h5>
    <div class="row g-3">@foreach ($related as $p)<div class="col-6 col-md-3">@include('shop._card', ['p' => $p])</div>@endforeach</div>
@endif
@endsection
