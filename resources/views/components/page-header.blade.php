@props([
    'title',
    'subtitle' => null,
    'breadcrumbs' => [],
    'actions' => null,
    'icon' => null
])

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
    <div class="mb-3 mb-md-0">
        @if(count($breadcrumbs) > 0)
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    @foreach($breadcrumbs as $breadcrumb)
                        @if($loop->last)
                            <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb['label'] }}</li>
                        @else
                            <li class="breadcrumb-item">
                                <a href="{{ $breadcrumb['url'] }}" class="text-decoration-none">{{ $breadcrumb['label'] }}</a>
                            </li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif
        
        <h1 class="h2 mb-0 d-flex align-items-center">
            @if($icon)
                <i class="fas fa-{{ $icon }} me-2 text-primary"></i>
            @endif
            {{ $title }}
        </h1>
        
        @if($subtitle)
            <p class="text-muted mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    
    @if($actions)
        <div class="d-flex gap-2 flex-wrap">
            {{ $actions }}
        </div>
    @endif
</div>