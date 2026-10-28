@php
    $flashVariants = ['success' => 'success', 'error' => 'error', 'info' => 'info', 'warning' => 'warning'];
    $flashKey = collect($flashVariants)->keys()->first(fn ($key) => session()->has($key));
@endphp

@if ($flashKey)
    <div id="rk-flash" class="d-none"
         data-message="{{ session($flashKey) }}"
         data-icon="{{ $flashVariants[$flashKey] }}"></div>

    <div class="alert alert-{{ $flashKey === 'error' ? 'danger' : $flashKey }} alert-dismissible fade show" role="alert">
        {{ session($flashKey) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong><i class="fas fa-circle-exclamation me-1"></i> Periksa kembali data yang Anda masukkan:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
@endif
