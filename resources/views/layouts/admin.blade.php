<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.partials-head')
    <title>@yield('title', 'Dashboard') · ForecastFlow Admin</title>
</head>
<body>
@php
    $alertCount = app(\App\Services\ForecastService::class)->analyze()->where('alert', true)->count();
    $pendingCount = \App\Models\Order::where('status', 'menunggu_verifikasi')->count();
    $nav = fn ($pattern) => request()->routeIs($pattern) ? 'active' : '';
@endphp
<div class="admin-wrap">
    <aside class="sidebar">
        <div class="brand"><span class="brand-mark"><i class="bi bi-graph-up-arrow"></i></span> ForecastFlow</div>

        <div class="label">Analitik</div>
        <a class="nav-i {{ $nav('admin.dashboard') }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a class="nav-i {{ $nav('admin.forecast.*') }}" href="{{ route('admin.forecast.index') }}"><i class="bi bi-activity"></i> Prediksi Stok
            @if ($alertCount)<span class="badge bg-danger rounded-pill">{{ $alertCount }}</span>@endif</a>

        <div class="label">Penjualan</div>
        <a class="nav-i {{ $nav('admin.orders.*') }}" href="{{ route('admin.orders.index') }}"><i class="bi bi-receipt"></i> Pesanan
            @if ($pendingCount)<span class="badge bg-warning text-dark rounded-pill">{{ $pendingCount }}</span>@endif</a>

        <div class="label">Inventaris</div>
        <a class="nav-i {{ $nav('admin.products.*') }}" href="{{ route('admin.products.index') }}"><i class="bi bi-box-seam"></i> Produk</a>
        <a class="nav-i {{ $nav('admin.categories.*') }}" href="{{ route('admin.categories.index') }}"><i class="bi bi-tags"></i> Kategori</a>
        <a class="nav-i {{ $nav('admin.suppliers.*') }}" href="{{ route('admin.suppliers.index') }}"><i class="bi bi-truck"></i> Supplier</a>
        <a class="nav-i {{ $nav('admin.stock.*') }}" href="{{ route('admin.stock.index') }}"><i class="bi bi-arrow-left-right"></i> Stok &amp; Mutasi</a>

        <div class="label">Pengadaan</div>
        <a class="nav-i {{ $nav('admin.po.*') }}" href="{{ route('admin.po.index') }}"><i class="bi bi-file-earmark-pdf"></i> Purchase Order</a>

        <div class="label">Lainnya</div>
        <a class="nav-i" href="{{ route('shop.index') }}" target="_blank"><i class="bi bi-shop"></i> Lihat Etalase</a>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="nav-i w-100 border-0 bg-transparent text-start" style="color:#fca5a5"><i class="bi bi-box-arrow-right"></i> Keluar</button>
        </form>
        <div class="small text-secondary px-2 mt-4">Masuk sebagai<br><strong class="text-white">{{ auth()->user()->name }}</strong></div>
    </aside>

    <main class="admin-main">
        @include('layouts.flash')
        @yield('content')
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@stack('scripts')
</body>
</html>
