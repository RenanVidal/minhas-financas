@props([
    'type' => 'info',
    'dismissible' => false,
    'icon' => null,
    'title' => null,
    'class' => ''
])

@php
$iconMap = [
    'success' => 'check-circle',
    'danger' => 'exclamation-circle',
    'warning' => 'exclamation-triangle',
    'info' => 'info-circle',
    'primary' => 'info-circle',
    'secondary' => 'info-circle',
    'light' => 'info-circle',
    'dark' => 'info-circle'
];

$defaultIcon = $iconMap[$type] ?? 'info-circle';
$alertIcon = $icon ?? $defaultIcon;
@endphp

<div class="alert alert-{{ $type }} {{ $dismissible ? 'alert-dismissible' : '' }} {{ $class }}" role="alert">
    <div class="d-flex align-items-start">
        <div class="flex-shrink-0">
            <i class="fas fa-{{ $alertIcon }} me-2"></i>
        </div>
        <div class="flex-grow-1">
            @if($title)
                <h6 class="alert-heading mb-1">{{ $title }}</h6>
            @endif
            <div>{{ $slot }}</div>
        </div>
        @if($dismissible)
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="Close"></button>
        @endif
    </div>
</div>