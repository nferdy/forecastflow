@extends('layouts.admin')
@section('title', 'Form Supplier')
@section('content')
<h2 class="page-title mb-4">{{ $supplier->exists ? 'Edit Supplier' : 'Tambah Supplier' }}</h2>
<form method="POST" action="{{ $supplier->exists ? route('admin.suppliers.update', $supplier) : route('admin.suppliers.store') }}">
@csrf @if ($supplier->exists) @method('PUT') @endif
<div class="card-soft p-4"><div class="row g-3">
    <div class="col-md-8"><label class="form-label">Nama supplier</label><input name="name" class="form-control" value="{{ old('name', $supplier->name) }}" required></div>
    <div class="col-md-4"><label class="form-label">Lead time (hari)</label><input type="number" name="lead_time_days" class="form-control" value="{{ old('lead_time_days', $supplier->lead_time_days) }}" min="1" required>
        <div class="form-text">Dipakai menghitung ROP.</div></div>
    <div class="col-md-6"><label class="form-label">Email (tujuan PO)</label><input type="email" name="email" class="form-control" value="{{ old('email', $supplier->email) }}" required></div>
    <div class="col-md-6"><label class="form-label">Telepon</label><input name="phone" class="form-control" value="{{ old('phone', $supplier->phone) }}"></div>
    <div class="col-12"><label class="form-label">Alamat</label><textarea name="address" rows="2" class="form-control">{{ old('address', $supplier->address) }}</textarea></div>
</div>
<div class="mt-4 d-flex gap-2"><button class="btn btn-brand px-4">Simpan</button><a href="{{ route('admin.suppliers.index') }}" class="btn btn-light">Batal</a></div></div>
</form>
@endsection
