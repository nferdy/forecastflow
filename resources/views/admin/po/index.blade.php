@extends('layouts.admin')
@section('title', 'Purchase Order')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div><h2 class="page-title mb-0">Pengadaan · Purchase Order</h2><div class="text-muted">Auto-PO membuat dokumen PDF &amp; mengirimkannya ke email supplier</div></div>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('admin.po.auto') }}">@csrf<button class="btn btn-brand"><i class="bi bi-lightning-charge-fill me-1"></i> Auto-PO</button></form>
        <a href="{{ route('admin.po.create') }}" class="btn btn-outline-brand"><i class="bi bi-plus-lg me-1"></i> PO Manual</a>
    </div>
</div>
<div class="card-soft p-3"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>No. PO</th><th>Supplier</th><th>Item</th><th class="text-end">Total</th><th>Status</th><th>Dibuat</th><th></th></tr></thead><tbody>
    @forelse ($pos as $po)
        <tr><td class="fw-semibold">{{ $po->po_number }}</td><td>{{ $po->supplier->name }}</td><td>{{ $po->items_count }} produk</td>
            <td class="text-end">{{ \App\Support\Fmt::rp($po->total) }}</td><td><span class="badge text-bg-{{ $po->status_color }}">{{ $po->status_label }}</span></td>
            <td class="small text-muted">{{ $po->created_at->translatedFormat('d M Y H:i') }}</td><td><a href="{{ route('admin.po.show', $po) }}" class="btn btn-sm btn-outline-brand">Buka</a></td></tr>
    @empty<tr><td colspan="7" class="text-center text-muted py-5">Belum ada PO. Klik <strong>Auto-PO</strong> untuk membuat dari rekomendasi prediksi.</td></tr>@endforelse
    </tbody></table></div></div>
<div class="mt-3">{{ $pos->links() }}</div>
@endsection
