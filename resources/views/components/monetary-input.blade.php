@props([
    'name',
    'value' => '',
    'placeholder' => 'R$ 0,00',
    'required' => false,
    'class' => '',
    'id' => null,
    'min' => '0.01',
    'step' => '0.01'
])

<input 
    type="text" 
    name="{{ $name }}" 
    id="{{ $id ?? $name }}"
    class="form-control monetary-input {{ $class }}" 
    value="{{ old($name, $value) }}" 
    placeholder="{{ $placeholder }}"
    {{ $required ? 'required' : '' }}
    inputmode="decimal"
    data-validation="positive-number"
    data-min="{{ $min }}"
    data-step="{{ $step }}"
    autocomplete="off"
    {{ $attributes }}
>