<div class="card product-card">
    <a href="{{ route('shop.show', $p) }}" class="text-decoration-none">
        <div class="thumb">
            @if ($p->image_url)<img src="{{ $p->image_url }}" alt="{{ $p->name }}">@else<i class="bi {{ $p->category->icon ?? 'bi-cpu' }}"></i>@endif
        </div>
    </a>
    <div class="card-body d-flex flex-column">
        <div class="small text-muted mb-1">{{ $p->category->name }}</div>
        <a href="{{ route('shop.show', $p) }}" class="text-decoration-none text-dark fw-semibold lh-sm mb-2" style="min-height:2.5em">{{ Str::limit($p->name, 48) }}</a>
        <div class="price mb-2">{{ \App\Support\Fmt::rp($p->price) }}</div>
        <div class="mt-auto d-flex align-items-center justify-content-between">
            @if ($p->stock <= 0)
                <span class="badge text-bg-danger">Stok habis</span>
            @elseif ($p->stock <= 5)
                <span class="badge text-bg-warning">Sisa {{ $p->stock }}</span>
            @else
                <span class="badge bg-success-subtle text-success-emphasis">Stok {{ $p->stock }}</span>
            @endif
            @if ($p->stock > 0)
                <form method="POST" action="{{ route('cart.add', $p) }}">@csrf
                    <button class="btn btn-brand btn-sm rounded-pill px-3" title="Tambah ke keranjang"><i class="bi bi-cart-plus"></i></button>
                </form>
            @endif
        </div>
    </div>
</div>
