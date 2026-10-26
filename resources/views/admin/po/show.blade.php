@extends('layouts.admin')
@section('title', $po->po_number)
@section('content')
<a href="{{ route('admin.po.index') }}" class="small">← Semua PO</a>
<div class="d-flex flex-wrap justify-content-between align-items-center my-3">
    <h2 class="page-title mb-0">{{ $po->po_number }}</h2><span class="badge text-bg-{{ $po->status_color }} fs-6">{{ $po->status_label }}</span>
</div>
<div class="row g-3">
    <div class="col-lg-8"><div class="card-soft p-3">
        <table class="table align-middle mb-0"><thead><tr><th>Produk</th><th class="text-end">Qty</th><th class="text-end">Harga beli</th><th class="text-end">Subtotal</th></tr></thead><tbody>
        @foreach ($po->items as $it)<tr><td>{{ $it->product->name }}<div class="small text-muted">{{ $it->product->sku }}</div></td><td class="text-end">{{ $it->qty }}</td><td class="text-end">{{ \App\Support\Fmt::rp($it->unit_cost) }}</td><td class="text-end">{{ \App\Support\Fmt::rp($it->subtotal) }}</td></tr>@endforeach
        </tbody><tfoot><tr><th colspan="3" class="text-end">Total</th><th class="text-end text-brand">{{ \App\Support\Fmt::rp($po->total) }}</th></tr></tfoot></table>
        @if ($po->notes)<div class="small text-muted border-top pt-2 mt-2"><strong>Catatan:</strong> {{ $po->notes }}</div>@endif
    </div></div>
    <div class="col-lg-4">
        <div class="card-soft p-4 mb-3"><h6 class="fw-bold">Supplier</h6>
            <div class="small">{{ $po->supplier->name }}<br><i class="bi bi-envelope"></i> {{ $po->supplier->email }}<br>Lead time: {{ $po->supplier->lead_time_days }} hari</div>
            @if ($po->sent_at)<hr><div class="small text-success"><i class="bi bi-send-check"></i> Terkirim {{ $po->sent_at->translatedFormat('d M Y H:i') }}</div>@endif
            @if ($po->received_at)<div class="small text-success"><i class="bi bi-box-seam"></i> Diterima {{ $po->received_at->translatedFormat('d M Y H:i') }}</div>@endif</div>
        <div class="card-soft p-4 d-grid gap-2">
            <a href="{{ route('admin.po.pdf', $po) }}" class="btn btn-outline-brand"><i class="bi bi-file-earmark-pdf me-1"></i> Unduh PDF</a>
            @if ($po->status !== 'diterima')
                <form method="POST" action="{{ route('admin.po.send', $po) }}">@csrf<button class="btn btn-brand w-100"><i class="bi bi-send me-1"></i> {{ $po->status === 'draft' ? 'Kirim ke Email Supplier' : 'Kirim Ulang Email' }}</button></form>
            @endif
            @if ($po->status === 'draft')
                <form method="POST" action="{{ route('admin.po.marksent', $po) }}">@csrf<button class="btn btn-light w-100 border">Tandai terkirim (manual)</button></form>
                <form method="POST" action="{{ route('admin.po.destroy', $po) }}" onsubmit="return confirm('Hapus draft PO?')">@csrf @method('DELETE')<button class="btn btn-outline-danger w-100">Hapus draft</button></form>
            @endif
            @if ($po->status === 'terkirim')
                <form method="POST" action="{{ route('admin.po.receive', $po) }}" onsubmit="return confirm('Konfirmasi barang sudah diterima? Stok akan bertambah.')">@csrf<button class="btn btn-success w-100"><i class="bi bi-box-arrow-in-down me-1"></i> Terima barang (tambah stok)</button></form>
            @endif
        </div>
    </div>
</div>
@endsection
