<!DOCTYPE html>
<html lang="id"><head>@include('layouts.partials-head')<title>Daftar · ForecastFlow</title></head>
<body>
<div class="auth-wrap p-3">
    <div class="card-soft p-4 p-md-5" style="width:100%;max-width:520px">
        <h4 class="fw-bold mb-1">Buat akun</h4>
        <p class="text-muted small">Untuk pelanggan perorangan (B2C) atau mitra toko (B2B).</p>
        @include('layouts.flash')
        <form method="POST" action="{{ route('register.store') }}">@csrf
            <div class="row g-3">
                <div class="col-12"><label class="form-label">Tipe akun</label>
                    <select name="account_type" id="type" class="form-select" onchange="document.getElementById('co').style.display=this.value==='B2B'?'block':'none'">
                        <option value="B2C" {{ old('account_type') === 'B2C' ? 'selected' : '' }}>Pelanggan perorangan (B2C)</option>
                        <option value="B2B" {{ old('account_type') === 'B2B' ? 'selected' : '' }}>Mitra toko / perusahaan (B2B)</option>
                    </select></div>
                <div class="col-12" id="co" style="display:{{ old('account_type') === 'B2B' ? 'block' : 'none' }}"><label class="form-label">Nama toko / perusahaan</label><input name="company_name" class="form-control" value="{{ old('company_name') }}"></div>
                <div class="col-md-6"><label class="form-label">Nama</label><input name="name" class="form-control" value="{{ old('name') }}" required></div>
                <div class="col-md-6"><label class="form-label">No. HP</label><input name="phone" class="form-control" value="{{ old('phone') }}"></div>
                <div class="col-12"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div>
                <div class="col-md-6"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
                <div class="col-md-6"><label class="form-label">Ulangi password</label><input type="password" name="password_confirmation" class="form-control" required></div>
            </div>
            <button class="btn btn-brand w-100 py-2 mt-4">Daftar</button>
        </form>
        <div class="text-center small mt-3">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></div>
    </div>
</div>
</body></html>
