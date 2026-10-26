@extends('layouts.admin')
@section('title', 'Supplier')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h2 class="page-title mb-0">Supplier</h2>
    <a href="{{ route('admin.suppliers.create') }}" class="btn btn-brand"><i class="bi bi-plus-lg me-1"></i> Tambah Supplier</a></div>
<div class="row g-3">
@foreach ($suppliers as $s)
    <div class="col-md-6 col-xl-4"><div class="card-soft p-4 h-100">
        <div class="d-flex justify-content-between"><h6 class="fw-bold">{{ $s->name }}</h6><span class="badge bg-warning-subtle text-warning-emphasis align-self-start">Lead {{ $s->lead_time_days }} hari</span></div>
        <div class="small text-muted mb-1"><i class="bi bi-envelope"></i> {{ $s->email }}</div>
        <div class="small text-muted mb-1"><i class="bi bi-telephone"></i> {{ $s->phone ?: '-' }}</div>
        <div class="small text-muted mb-3"><i class="bi bi-geo-alt"></i> {{ $s->address ?: '-' }}</div>
        <div class="d-flex justify-content-between align-items-center"><span class="small">{{ $s->products_count }} produk</span>
            <div><a href="{{ route('admin.suppliers.edit', $s) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
            <form class="d-inline" method="POST" action="{{ route('admin.suppliers.destroy', $s) }}" onsubmit="return confirm('Hapus supplier?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></div></div>
    </div></div>
@endforeach
</div>
@endsection
