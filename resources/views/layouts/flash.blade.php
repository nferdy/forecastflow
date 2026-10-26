@foreach (['success' => 'success', 'error' => 'danger', 'info' => 'info'] as $key => $color)
    @if (session($key))
        <div class="alert alert-{{ $color }} alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
            <i class="bi bi-{{ $key === 'success' ? 'check-circle' : ($key === 'error' ? 'exclamation-octagon' : 'info-circle') }} me-1"></i>
            {{ session($key) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
@endforeach
@if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm rounded-4">
        <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Periksa kembali isian Anda:</div>
        <ul class="mb-0 small">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif
