<!DOCTYPE html>
<html lang="id">
<head>
    @include('layouts.partials-head')
    <title>@yield('title', 'Katalog') · VSD Lab Store</title>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-shop sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ route('shop.index') }}">
            <span class="brand-mark"><i class="bi bi-cpu-fill"></i></span>
            <span>VSD<span class="text-brand">Lab</span> Store</span>
        </a>
        <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <form class="d-flex ms-lg-4 my-2 my-lg-0 flex-grow-1" style="max-width:420px" action="{{ route('shop.index') }}">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                    <input class="form-control border-start-0" name="q" value="{{ request('q') }}" placeholder="Cari RAM, SSD, motherboard...">
                </div>
            </form>
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="{{ route('shop.index') }}">Katalog</a></li>
                <li class="nav-item">
                    <a class="nav-link position-relative" href="{{ route('cart.index') }}">
                        <i class="bi bi-cart3 fs-5"></i>
                        @if (count(session('cart', [])))
                            <span class="badge rounded-pill bg-danger position-absolute top-0 start-100 translate-middle">{{ count(session('cart', [])) }}</span>
                        @endif
                    </a>
                </li>
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle fw-semibold" href="#" data-bs-toggle="dropdown"><i class="bi bi-person-circle"></i> {{ Str::words(auth()->user()->name, 1, '') }}</a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4">
                            @if (auth()->user()->isAdmin())
                                <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard Admin</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('orders.index') }}"><i class="bi bi-bag-check me-2"></i>Pesanan Saya</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">@csrf
                                    <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Keluar</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item"><a class="btn btn-outline-brand btn-sm px-3" href="{{ route('login') }}">Masuk</a></li>
                    <li class="nav-item"><a class="btn btn-brand btn-sm px-3" href="{{ route('register') }}">Daftar</a></li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    @include('layouts.flash')
    @yield('content')
</main>

<footer class="footer-shop mt-5 py-4">
    <div class="container d-flex flex-column flex-md-row justify-content-between gap-2 small">
        <div><strong class="text-white">Vortex Supply Dynamics (VSD Lab)</strong><br>Techno-Park Hub, Blater, Purbalingga, Jawa Tengah</div>
        <div class="text-md-end">Powered by <strong class="text-white">ForecastFlow</strong><br><em>Predictive Analytics, Precision Supply Chain</em></div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
