@props([
    'title' => null,
    'subtitle' => null,
    'headerActions' => null,
    'footer' => null,
    'class' => '',
    'bodyClass' => '',
    'headerClass' => ''
])

<div class="card {{ $class }}">
    @if($title || $subtitle || $headerActions)
        <div class="card-header {{ $headerClass }}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    @if($title)
                        <h5 class="card-title mb-0">{{ $title }}</h5>
                    @endif
                    @if($subtitle)
                        <small class="text-muted">{{ $subtitle }}</small>
                    @endif
                </div>
                @if($headerActions)
                    <div>
                        {{ $headerActions }}
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>
    
    @if($footer)
        <div class="card-footer">
            {{ $footer }}
        </div>
    @endif
</div>