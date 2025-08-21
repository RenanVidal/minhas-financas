@props([
    'headers' => [],
    'rows' => [],
    'actions' => null,
    'emptyMessage' => 'Nenhum registro encontrado.',
    'responsive' => true,
    'striped' => true,
    'hover' => true,
    'class' => ''
])

<div class="{{ $responsive ? 'table-responsive' : '' }}">
    <table class="table {{ $striped ? 'table-striped' : '' }} {{ $hover ? 'table-hover' : '' }} {{ $class }}">
        @if(count($headers) > 0)
            <thead class="table-light">
                <tr>
                    @foreach($headers as $header)
                        <th class="{{ $header['class'] ?? '' }}">
                            {{ $header['label'] ?? $header }}
                        </th>
                    @endforeach
                    @if($actions)
                        <th class="text-center" width="120">Ações</th>
                    @endif
                </tr>
            </thead>
        @endif
        
        <tbody>
            @forelse($rows as $row)
                <tr>
                    {{ $row }}
                    @if($actions)
                        <td class="text-center">
                            {{ $actions($loop->parent->value ?? $row) }}
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) + ($actions ? 1 : 0) }}" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        {{ $emptyMessage }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>