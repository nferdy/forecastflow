@extends('layouts.admin')
@section('title', 'Pesanan')
@section('content')
<h2 class="page-title mb-4">Manajemen Pesanan</h2>
<form class="row g-2 mb-3" method="GET">
    <div class="col-md-4"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="No. pesanan / nama penerima"></div>
    <div class="col-md-3"><select name="status" class="form-select" onchange="this.form.submit()"><option value="">Semua status</option>
        @foreach (\App\Models\Order::STATUSES as $k => $v)<option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $v[0] }}</option>@endforeach</select></div>
    <div class="col-auto"><button class="btn btn-outline-secondary">Cari</button></div>
</form>
<div class="card-soft p-3"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>No. Pesanan</th><th>Pelanggan</th><th>Item</th><th class="text-end">Total</th><th>Status</th><th>Waktu</th><th></th></tr></thead><tbody>
    @forelse ($orders as $o)
        <tr><td class="fw-semibold">{{ $o->order_number }}</td>
            <td>{{ $o->user->name }} <span class="badge bg-light text-dark border">{{ $o->user->account_type }}</span></td>
            <td>{{ $o->items_count }}</td><td class="text-end">{{ \App\Support\Fmt::rp($o->total) }}</td>
            <td><span class="badge text-bg-{{ $o->status_color }}">{{ $o->status_label }}</span></td>
            <td class="small text-muted">{{ $o->created_at->translatedFormat('d M Y H:i') }}</td>
            <td><a href="{{ route('admin.orders.show', $o) }}" class="btn btn-sm btn-outline-brand">Kelola</a></td></tr>
    @empty<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada pesanan.</td></tr>@endforelse
    </tbody></table></div></div>
<div class="mt-3">{{ $orders->links() }}</div>
@endsection
