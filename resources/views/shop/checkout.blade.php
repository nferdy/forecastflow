@extends('layouts.shop')
@section('title', 'Checkout')
@section('content')
<h3 class="fw-bold mb-4"><i class="bi bi-credit-card me-2 text-brand"></i>Checkout</h3>
<form method="POST" action="{{ route('checkout.store') }}">@csrf
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-soft p-4 mb-3">
            <h5 class="fw-bold mb-3">Data pengiriman</h5>
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nama penerima</label>
                    <input name="shipping_name" class="form-control" value="{{ old('shipping_name', auth()->user()->name) }}" required></div>
                <div class="col-md-6"><label class="form-label">No. HP / WhatsApp</label>
                    <input name="shipping_phone" class="form-control" value="{{ old('shipping_phone', auth()->user()->phone) }}" required></div>
                <div class="col-12"><label class="form-label">Alamat lengkap</label>
                    <textarea name="shipping_address" rows="3" class="form-control" required>{{ old('shipping_address', auth()->user()->address) }}</textarea></div>
                <div class="col-md-6"><label class="form-label">Ekspedisi</label>
                    <select name="courier" class="form-select">
                        @foreach (['JNE', 'J&T', 'SiCepat', 'AnterAja', 'Ambil di Toko'] as $c)<option {{ old('courier') === $c ? 'selected' : '' }}>{{ $c }}</option>@endforeach
                    </select></div>
                <div class="col-md-6"><label class="form-label">Catatan (opsional)</label>
                    <input name="notes" class="form-control" value="{{ old('notes') }}"></div>
            </div>
        </div>
        <div class="card-soft p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-bank me-1"></i> Pembayaran: Transfer Bank</h5>
            <p class="mb-1">Bank BCA · <strong>1234 567 890</strong></p>
            <p class="text-muted small mb-0">a.n. Vortex Supply Dynamics (rekening contoh). Setelah checkout, unggah bukti transfer di halaman pesanan agar diverifikasi admin.</p>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card-soft p-4">
            <h5 class="fw-bold mb-3">Pesanan Anda</h5>
            @foreach ($lines as $l)
                <div class="d-flex justify-content-between small mb-2"><span>{{ $l['qty'] }} × {{ Str::limit($l['product']->name, 30) }}</span><span>{{ \App\Support\Fmt::rp($l['subtotal']) }}</span></div>
            @endforeach
            <div class="d-flex justify-content-between fs-5 fw-bold border-top pt-3 mt-3"><span>Total</span><span class="text-brand">{{ \App\Support\Fmt::rp($total) }}</span></div>
            <button class="btn btn-brand w-100 mt-3 py-2"><i class="bi bi-lock me-1"></i> Buat Pesanan</button>
            <div class="small text-muted mt-2">Stok langsung dialokasikan untuk pesanan Anda saat checkout berhasil.</div>
        </div>
    </div>
</div>
</form>
@endsection
