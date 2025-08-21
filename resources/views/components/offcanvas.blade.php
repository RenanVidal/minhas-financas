@props([
    'id',
    'title' => null,
    'placement' => 'start',
    'backdrop' => 'true',
    'scroll' => 'false',
    'class' => ''
])

<div class="offcanvas offcanvas-{{ $placement }} {{ $class }}" tabindex="-1" id="{{ $id }}" aria-labelledby="{{ $id }}Label" data-bs-backdrop="{{ $backdrop }}" data-bs-scroll="{{ $scroll }}">
    @if($title)
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="{{ $id }}Label">{{ $title }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
    @endif
    
    <div class="offcanvas-body">
        {{ $slot }}
    </div>
</div>