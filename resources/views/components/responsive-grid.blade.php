@props([
    'cols' => 'auto',
    'gap' => '3',
    'class' => ''
])

@php
$colsClass = match($cols) {
    '1' => 'row-cols-1',
    '2' => 'row-cols-1 row-cols-md-2',
    '3' => 'row-cols-1 row-cols-md-2 row-cols-lg-3',
    '4' => 'row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4',
    '5' => 'row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5',
    '6' => 'row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-lg-6',
    'auto' => 'row-cols-auto',
    default => $cols
};

$gapClass = 'g-' . $gap;
@endphp

<div class="row {{ $colsClass }} {{ $gapClass }} {{ $class }}">
    {{ $slot }}
</div>