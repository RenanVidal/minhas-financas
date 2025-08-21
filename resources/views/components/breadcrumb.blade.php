@props([
    'items' => [],
    'class' => ''
])

@if(count($items) > 0)
    <nav aria-label="breadcrumb" class="{{ $class }}">
        <ol class="breadcrumb mb-0">
            @foreach($items as $item)
                @if($loop->last)
                    <li class="breadcrumb-item active" aria-current="page">
                        <span class="d-none d-sm-inline">{{ $item['label'] }}</span>
                        <span class="d-sm-none">{{ Str::limit($item['label'], 15) }}</span>
                    </li>
                @else
                    <li class="breadcrumb-item">
                        <a href="{{ $item['url'] }}" class="text-decoration-none">
                            @if(isset($item['icon']))
                                <i class="fas fa-{{ $item['icon'] }} me-1"></i>
                            @endif
                            <span class="d-none d-sm-inline">{{ $item['label'] }}</span>
                            <span class="d-sm-none">{{ Str::limit($item['label'], 10) }}</span>
                        </a>
                    </li>
                @endif
            @endforeach
        </ol>
    </nav>
@endif