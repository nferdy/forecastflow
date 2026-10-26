@extends('layouts.admin')
@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')
@section('content')
<h2 class="page-title mb-4">{{ $product->exists ? 'Edit Produk' : 'Tambah Produk' }}</h2>
<form method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
@csrf @if ($product->exists) @method('PUT') @endif
<div class="card-soft p-4"><div class="row g-3">
    <div class="col-md-8"><label class="form-label">Nama produk</label><input name="name" class="form-control" value="{{ old('name', $product->name) }}" required></div>
    <div class="col-md-4"><label class="form-label">SKU</label><input name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" required></div>
    <div class="col-md-6"><label class="form-label">Kategori</label><select name="category_id" class="form-select">@foreach ($categories as $c)<option value="{{ $c->id }}" {{ old('category_id', $product->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label">Supplier</label><select name="supplier_id" class="form-select">@foreach ($suppliers as $s)<option value="{{ $s->id }}" {{ old('supplier_id', $product->supplier_id) == $s->id ? 'selected' : '' }}>{{ $s->name }} (lead {{ $s->lead_time_days }} hr)</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label">Harga jual (Rp)</label><input type="number" name="price" class="form-control" value="{{ old('price', $product->price) }}" min="0" required></div>
    <div class="col-md-4"><label class="form-label">Harga beli / modal (Rp)</label><input type="number" name="cost_price" class="form-control" value="{{ old('cost_price', $product->cost_price) }}" min="0" required></div>
    <div class="col-md-4"><label class="form-label">Stok {{ $product->exists ? '(ubah via Stok & Mutasi)' : 'awal' }}</label>
        @if ($product->exists)<input class="form-control" value="{{ $product->stock }}" disabled>@else<input type="number" name="stock" class="form-control" value="{{ old('stock', 0) }}" min="0" required>@endif</div>
    <div class="col-12"><label class="form-label">Deskripsi</label><textarea name="description" rows="3" class="form-control">{{ old('description', $product->description) }}</textarea></div>
    <div class="col-md-6"><label class="form-label">Foto produk (opsional)</label><input type="file" name="image" class="form-control" accept="image/*">
        @if ($product->image_url)<img src="{{ $product->image_url }}" class="rounded-3 mt-2" style="height:70px">@endif</div>
    <div class="col-md-6 d-flex align-items-end"><div class="form-check form-switch"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="act" {{ old('is_active', $product->is_active) ? 'checked' : '' }}><label class="form-check-label" for="act">Tampilkan di etalase</label></div></div>
</div>
<div class="mt-4 d-flex gap-2"><button class="btn btn-brand px-4">Simpan</button><a href="{{ route('admin.products.index') }}" class="btn btn-light">Batal</a></div></div>
</form>
@endsection
