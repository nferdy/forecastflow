@extends('layouts.shop')
@section('title', 'Keranjang')
@section('content')
<h3 class="fw-bold mb-4"><i class="bi bi-cart3 me-2 text-brand"></i>Keranjang Belanja</h3>
@if (! count($lines))
    <div class="card-soft text-center p-5">
        <i class="bi bi-cart-x display-4 text-muted"></i>
        <p class="mt-3 text-muted">Keranjang Anda masih kosong.</p>
        <a href="{{ route('shop.index') }}" class="btn btn-brand mx-auto" style="width:fit-content">Mulai Belanja</a>
    </div>
@else
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-soft p-2">
            <table class="table align-middle mb-0">
                <thead><tr><th>Produk</th><th>Harga</th><th style="width:130px">Jumlah</th><th class="text-end">Subtotal</th><th></th></tr></thead>
                <tbody>
                @foreach ($lines as $l)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="mini-thumb">@if ($l['product']->image_url)<img src="{{ $l['product']->image_url }}">@else<i class="bi {{ $l['product']->category->icon ?? 'bi-cpu' }}"></i>@endif</span>
                                <div><a href="{{ route('shop.show', $l['product']) }}" class="fw-semibold text-dark text-decoration-none">{{ Str::limit($l['product']->name, 40) }}</a>
                                    <div class="small text-muted">Stok: {{ $l['product']->stock }}</div></div>
                            </div>
                        </td>
                        <td>{{ \App\Support\Fmt::rp($l['product']->price) }}</td>
                        <td>
                            <form method="POST" action="{{ route('cart.update', $l['product']) }}" class="d-flex gap-1">@csrf @method('PATCH')
                                <input type="number" name="qty" value="{{ $l['qty'] }}" min="1" max="{{ $l['product']->stock }}" class="form-control form-control-sm">
                                <button class="btn btn-sm btn-outline-secondary" title="Perbarui"><i class="bi bi-arrow-repeat"></i></button>
                            </form>
                        </td>
                        <td class="text-end fw-semibold">{{ \App\Support\Fmt::rp($l['subtotal']) }}</td>
                        <td>
                            <form method="POST" action="{{ route('cart.remove', $l['product']) }}">@csrf @method('DELETE')
                                <button class="btn btn-sm text-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-soft p-4">
            <h5 class="fw-bold mb-3">Ringkasan</h5>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Total barang</span><span>{{ collect($lines)->sum('qty') }} unit</span></div>
            <div class="d-flex justify-content-between fs-5 fw-bold border-top pt-3"><span>Total</span><span class="text-brand">{{ \App\Support\Fmt::rp($total) }}</span></div>
            <a href="{{ route('checkout.show') }}" class="btn btn-brand w-100 mt-3 py-2">Lanjut Checkout <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</div>
@endif
@endsection
