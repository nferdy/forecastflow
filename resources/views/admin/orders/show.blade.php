@extends('layouts.admin')
@section('title', $order->order_number)
@section('content')
<a href="{{ route('admin.orders.index') }}" class="small">← Semua pesanan</a>
<div class="d-flex flex-wrap justify-content-between align-items-center my-3">
    <h2 class="page-title mb-0">{{ $order->order_number }}</h2><span class="badge text-bg-{{ $order->status_color }} fs-6">{{ $order->status_label }}</span>
</div>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card-soft p-3 mb-3"><table class="table align-middle mb-0">
            <thead><tr><th>Produk</th><th>Harga</th><th>Qty</th><th class="text-end">Subtotal</th></tr></thead><tbody>
            @foreach ($order->items as $it)<tr><td>{{ $it->product->name }}</td><td>{{ \App\Support\Fmt::rp($it->price) }}</td><td>{{ $it->qty }}</td><td class="text-end">{{ \App\Support\Fmt::rp($it->subtotal) }}</td></tr>@endforeach
            </tbody><tfoot><tr><th colspan="3" class="text-end">Total</th><th class="text-end text-brand">{{ \App\Support\Fmt::rp($order->total) }}</th></tr></tfoot></table></div>

        <div class="card-soft p-4">
            <h6 class="fw-bold">Tindakan admin</h6>
            @switch($order->status)
                @case('menunggu_pembayaran')<p class="text-muted small mb-0">Menunggu pelanggan mengunggah bukti transfer.</p>@break
                @case('menunggu_verifikasi')
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('admin.orders.update', $order) }}">@csrf @method('PATCH')<input type="hidden" name="aksi" value="verifikasi"><button class="btn btn-success"><i class="bi bi-check2-circle me-1"></i> Verifikasi pembayaran</button></form>
                        <form method="POST" action="{{ route('admin.orders.update', $order) }}">@csrf @method('PATCH')<input type="hidden" name="aksi" value="tolak"><button class="btn btn-outline-warning">Tolak bukti</button></form>
                    </div>@break
                @case('diproses')
                    <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="row g-2">@csrf @method('PATCH')<input type="hidden" name="aksi" value="kirim">
                        <div class="col-md-4"><input name="courier" class="form-control" value="{{ $order->courier }}" placeholder="Ekspedisi" required></div>
                        <div class="col-md-5"><input name="tracking_number" class="form-control" placeholder="Nomor resi (input manual)" required></div>
                        <div class="col-md-3"><button class="btn btn-brand w-100"><i class="bi bi-truck me-1"></i> Kirim</button></div>
                    </form>@break
                @case('dikirim')
                    <form method="POST" action="{{ route('admin.orders.update', $order) }}">@csrf @method('PATCH')<input type="hidden" name="aksi" value="selesai"><button class="btn btn-success">Tandai selesai</button></form>@break
                @default<p class="text-muted small mb-0">Tidak ada tindakan lanjutan.</p>
            @endswitch
            @unless (in_array($order->status, ['selesai', 'dibatalkan']))
                <hr><form method="POST" action="{{ route('admin.orders.update', $order) }}" onsubmit="return confirm('Batalkan pesanan & kembalikan stok?')">@csrf @method('PATCH')<input type="hidden" name="aksi" value="batal"><button class="btn btn-sm btn-outline-danger">Batalkan pesanan (stok dikembalikan)</button></form>
            @endunless
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-soft p-4 mb-3"><h6 class="fw-bold">Pelanggan</h6>
            <div class="small">{{ $order->user->name }} <span class="badge bg-light text-dark border">{{ $order->user->account_type }}</span>@if ($order->user->company_name)<br>{{ $order->user->company_name }}@endif<br>{{ $order->user->email }}</div>
            <hr><h6 class="fw-bold">Pengiriman</h6><div class="small">{{ $order->shipping_name }} · {{ $order->shipping_phone }}<br>{{ $order->shipping_address }}<br>
                Ekspedisi: <strong>{{ $order->courier }}</strong><br>Resi: <strong>{{ $order->tracking_number ?: '-' }}</strong></div>
            @if ($order->notes)<hr><div class="small"><strong>Catatan:</strong> {{ $order->notes }}</div>@endif</div>
        <div class="card-soft p-4"><h6 class="fw-bold">Bukti transfer</h6>
            @if ($order->payment_proof)
                @if (Str::endsWith($order->payment_proof, '.pdf'))<a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" class="btn btn-outline-secondary btn-sm">Buka PDF</a>
                @else<a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank"><img src="{{ asset('storage/' . $order->payment_proof) }}" class="img-fluid rounded-3 border"></a>@endif
            @else<p class="small text-muted mb-0">Belum ada bukti.</p>@endif</div>
    </div>
</div>
@endsection
