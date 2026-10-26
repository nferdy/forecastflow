@extends('layouts.shop')
@section('title', $order->order_number)
@section('content')
@php
    $flow = ['menunggu_pembayaran' => 'Menunggu Pembayaran', 'menunggu_verifikasi' => 'Verifikasi', 'diproses' => 'Diproses', 'dikirim' => 'Dikirim', 'selesai' => 'Selesai'];
    $idx = array_search($order->status, array_keys($flow));
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h3 class="fw-bold mb-0">{{ $order->order_number }}</h3>
    <span class="badge text-bg-{{ $order->status_color }} fs-6">{{ $order->status_label }}</span>
</div>

@if ($order->status !== 'dibatalkan')
    <div class="timeline mb-4">
        @foreach ($flow as $key => $label)
            <div class="step {{ $idx !== false && array_search($key, array_keys($flow)) <= $idx ? 'done' : '' }}">{{ $label }}</div>
        @endforeach
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-soft p-3 mb-3">
            <table class="table mb-0 align-middle">
                <thead><tr><th>Produk</th><th>Harga</th><th>Qty</th><th class="text-end">Subtotal</th></tr></thead>
                <tbody>
                @foreach ($order->items as $it)
                    <tr><td>{{ $it->product->name }}</td><td>{{ \App\Support\Fmt::rp($it->price) }}</td><td>{{ $it->qty }}</td><td class="text-end">{{ \App\Support\Fmt::rp($it->subtotal) }}</td></tr>
                @endforeach
                </tbody>
                <tfoot><tr><th colspan="3" class="text-end">Total</th><th class="text-end text-brand">{{ \App\Support\Fmt::rp($order->total) }}</th></tr></tfoot>
            </table>
        </div>

        @if (in_array($order->status, ['menunggu_pembayaran', 'menunggu_verifikasi']))
            <div class="card-soft p-4">
                <h5 class="fw-bold"><i class="bi bi-upload me-1"></i> Unggah bukti transfer</h5>
                <p class="small text-muted">Transfer <strong>{{ \App\Support\Fmt::rp($order->total) }}</strong> ke BCA 1234 567 890 a.n. Vortex Supply Dynamics.</p>
                <form method="POST" action="{{ route('orders.proof', $order) }}" enctype="multipart/form-data" class="d-flex flex-wrap gap-2">@csrf
                    <input type="file" name="payment_proof" class="form-control" style="max-width:360px" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                    <button class="btn btn-brand">Kirim Bukti</button>
                </form>
                @if ($order->payment_proof)<div class="small text-success mt-2"><i class="bi bi-check-circle"></i> Bukti sudah terunggah — menunggu verifikasi admin.</div>@endif
            </div>
        @endif
    </div>
    <div class="col-lg-4">
        <div class="card-soft p-4 mb-3">
            <h6 class="fw-bold">Pengiriman</h6>
            <div class="small">{{ $order->shipping_name }} · {{ $order->shipping_phone }}<br>{{ $order->shipping_address }}</div>
            <hr>
            <div class="small">Ekspedisi: <strong>{{ $order->courier }}</strong></div>
            <div class="small">No. Resi: <strong>{{ $order->tracking_number ?: 'Belum tersedia' }}</strong></div>
        </div>
        @if ($order->status === 'menunggu_pembayaran')
            <form method="POST" action="{{ route('orders.cancel', $order) }}" onsubmit="return confirm('Batalkan pesanan ini?')">@csrf
                <button class="btn btn-outline-danger w-100">Batalkan Pesanan</button>
            </form>
        @endif
    </div>
</div>
@endsection
