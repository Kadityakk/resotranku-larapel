@props(['icon' => 'fa-inbox', 'title' => 'Belum ada data', 'message' => null])

<div {{ $attributes->merge(['class' => 'text-center py-5']) }}>
    <i class="fas {{ $icon }} fa-3x text-body-tertiary mb-3"></i>
    <h5 class="mb-1">{{ $title }}</h5>
    @if ($message)
        <p class="text-body-secondary mb-3">{{ $message }}</p>
    @endif
    {{ $slot }}
</div>
