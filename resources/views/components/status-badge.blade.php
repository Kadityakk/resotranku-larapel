@props(['status'])

<span {{ $attributes->merge(['class' => 'badge rounded-pill text-bg-'.$status->color()]) }}>
    @if (method_exists($status, 'icon'))
        <i class="fas {{ $status->icon() }} me-1"></i>
    @endif
    {{ $status->label() }}
</span>
