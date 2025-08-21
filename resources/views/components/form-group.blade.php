@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'help' => null,
    'error' => null,
    'class' => 'mb-3'
])

<div class="{{ $class }}">
    @if($label)
        <label for="{{ $name }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif
    
    {{ $slot }}
    
    @if($help)
        <div class="form-text">{{ $help }}</div>
    @endif
    
    @if($error || ($name && $errors->has($name)))
        <div class="invalid-feedback d-block">
            {{ $error ?? $errors->first($name) }}
        </div>
    @endif
</div>