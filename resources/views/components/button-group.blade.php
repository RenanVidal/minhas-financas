@props([
    'vertical' => false,
    'size' => null,
    'class' => ''
])

@php
$groupClass = $vertical ? 'btn-group-vertical' : 'btn-group';
$sizeClass = $size ? 'btn-group-' . $size : '';
@endphp

<div class="{{ $groupClass }} {{ $sizeClass }} {{ $class }}" role="group" {{ $attributes }}>
    {{ $slot }}
</div>

@push('styles')
<style>
@media (max-width: 576px) {
    .btn-group:not(.btn-group-vertical) {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-group:not(.btn-group-vertical) .btn {
        border-radius: 0.375rem !important;
        margin-bottom: 0.25rem;
    }
    
    .btn-group:not(.btn-group-vertical) .btn:last-child {
        margin-bottom: 0;
    }
}
</style>
@endpush