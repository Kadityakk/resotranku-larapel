@props(['value' => 0])

<span {{ $attributes }}>Rp{{ number_format((int) $value, 0, ',', '.') }}</span>
