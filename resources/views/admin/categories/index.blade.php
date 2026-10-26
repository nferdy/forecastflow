@extends('layouts.admin')
@section('title', 'Kategori')
@section('content')
<h2 class="page-title mb-4">Kategori Produk</h2>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card-soft p-4"><h6 class="fw-bold">Tambah kategori</h6>
            <form method="POST" action="{{ route('admin.categories.store') }}">@csrf
                <input name="name" class="form-control mb-2" placeholder="Nama kategori" required>
                <input name="icon" class="form-control mb-2" placeholder="Ikon Bootstrap (mis. bi-cpu)">
                <button class="btn btn-brand w-100">Tambah</button>
            </form>
            <div class="small text-muted mt-2">Daftar ikon: <a href="https://icons.getbootstrap.com" target="_blank">icons.getbootstrap.com</a></div>
        </div>
    </div>
    <div class="col-lg-8"><div class="card-soft p-3"><table class="table align-middle mb-0">
        <thead><tr><th>Ikon</th><th>Nama</th><th>Produk</th><th></th></tr></thead><tbody>
        @foreach ($categories as $c)
            <tr>
                <td style="width:160px"><div class="input-group input-group-sm"><span class="input-group-text"><i class="bi {{ $c->icon }}"></i></span><input form="upd{{ $c->id }}" name="icon" value="{{ $c->icon }}" class="form-control"></div></td>
                <td><input form="upd{{ $c->id }}" name="name" value="{{ $c->name }}" class="form-control form-control-sm"></td>
                <td>{{ $c->products_count }}</td>
                <td class="text-nowrap text-end">
                    <button form="upd{{ $c->id }}" class="btn btn-sm btn-outline-secondary" title="Simpan"><i class="bi bi-check2"></i></button>
                    <button form="del{{ $c->id }}" class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        @endforeach
        </tbody></table>
        @foreach ($categories as $c)
            <form id="upd{{ $c->id }}" method="POST" action="{{ route('admin.categories.update', $c) }}">@csrf @method('PUT')</form>
            <form id="del{{ $c->id }}" method="POST" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('Hapus kategori?')">@csrf @method('DELETE')</form>
        @endforeach
        </div></div>
</div>
@endsection
