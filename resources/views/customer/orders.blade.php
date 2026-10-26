@extends('layouts.shop')
@section('title', 'Pesanan Saya')
@section('content')
<h3 class="fw-bold mb-4"><i class="bi bi-bag-check me-2 text-brand"></i>Pesanan Saya</h3>
<div class="card-soft p-2">
<table class="table align-middle mb-0">
    <thead><tr><th>No. Pesanan</th><th>Tanggal</th><th>Item</th><th>Total</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse ($orders as $o)
        <tr>
            <td class="fw-semibold">{{ $o->order_number }}</td>
            <td>{{ $o->created_at->translatedFormat('d M Y H:i') }}</td>
            <td>{{ $o->items_count }} produk</td>
            <td>{{ \App\Support\Fmt::rp($o->total) }}</td>
            <td><span class="badge text-bg-{{ $o->status_color }}">{{ $o->status_label }}</span></td>
            <td class="text-end"><a href="{{ route('orders.show', $o) }}" class="btn btn-sm btn-outline-brand">Detail</a></td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada pesanan.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
<div class="mt-3">{{ $orders->links() }}</div>
@endsection
