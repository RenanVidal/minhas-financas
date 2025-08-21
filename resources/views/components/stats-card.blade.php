@props([
    'title',
    'value',
    'icon',
    'color' => 'primary',
    'trend' => null,
    'trendIcon' => null,
    'subtitle' => null
])

<div class="card text-white bg-{{ $color }} h-100">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <h6 class="card-title text-white-50 mb-1">{{ $title }}</h6>
                <h3 class="mb-0 fw-bold">{{ $value }}</h3>
                
                @if($subtitle)
                    <small class="text-white-50">{{ $subtitle }}</small>
                @endif
                
                @if($trend)
                    <div class="mt-2">
                        <small class="text-white-50">
                            @if($trendIcon)
                                <i class="fas fa-{{ $trendIcon }} me-1"></i>
                            @endif
                            {{ $trend }}
                        </small>
                    </div>
                @endif
            </div>
            
            <div class="align-self-center ms-3">
                <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fas fa-{{ $icon }} fa-lg text-white"></i>
                </div>
            </div>
        </div>
    </div>
</div>