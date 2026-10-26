<!DOCTYPE html>
<html lang="id"><head>@include('layouts.partials-head')<title>Masuk · ForecastFlow</title></head>
<body>
<div class="auth-wrap p-3">
    <div class="card-soft p-4 p-md-5" style="width:100%;max-width:440px">
        <div class="text-center mb-4">
            <span class="brand-mark mb-2" style="width:52px;height:52px;font-size:1.6rem"><i class="bi bi-graph-up-arrow"></i></span>
            <h4 class="fw-bold mb-0">ForecastFlow</h4>
            <small class="text-muted">Predictive Analytics, Precision Supply Chain</small>
        </div>
        @include('layouts.flash')
        <form method="POST" action="{{ route('login.store') }}">@csrf
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus></div>
            <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
            <div class="form-check mb-3"><input type="checkbox" name="remember" class="form-check-input" id="r"><label for="r" class="form-check-label small">Ingat saya</label></div>
            <button class="btn btn-brand w-100 py-2">Masuk</button>
        </form>
        <div class="text-center small mt-3">Belum punya akun? <a href="{{ route('register') }}">Daftar</a> · <a href="{{ route('shop.index') }}">Ke etalase</a></div>
        <div class="alert alert-light border small mt-4 mb-0">
            <strong>Akun demo</strong> (password: <code>password</code>)<br>
            Admin: <code>admin@vsdlab.test</code><br>Pelanggan: <code>pelanggan@vsdlab.test</code>
        </div>
    </div>
</div>
</body></html>
