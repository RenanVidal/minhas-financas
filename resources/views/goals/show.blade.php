<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $goal->name }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('goals.edit', $goal) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Editar
                </a>
                <form method="POST" action="{{ route('goals.destroy', $goal) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" 
                            onclick="return confirm('Tem certeza que deseja excluir esta meta?')">
                        Excluir
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Goal Overview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Goal Details -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Detalhes da Meta</h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="font-medium">Nome:</span> {{ $goal->name }}
                                </div>
                                <div>
                                    <span class="font-medium">Valor Alvo:</span> R$ {{ number_format($goal->target_amount, 2, ',', '.') }}
                                </div>
                                <div>
                                    <span class="font-medium">Valor Atual:</span> R$ {{ number_format($goal->current_amount, 2, ',', '.') }}
                                </div>
                                <div>
                                    <span class="font-medium">Data Limite:</span> {{ $goal->deadline->format('d/m/Y') }}
                                </div>
                                <div>
                                    <span class="font-medium">Status:</span>
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full
                                        @if($goal->status === 'completed') bg-green-100 text-green-800
                                        @elseif($goal->status === 'cancelled') bg-red-100 text-red-800
                                        @else bg-blue-100 text-blue-800 @endif">
                                        @if($goal->status === 'completed') Concluída
                                        @elseif($goal->status === 'cancelled') Cancelada
                                        @else Ativa @endif
                                    </span>
                                </div>
                                @if($goal->category)
                                    <div>
                                        <span class="font-medium">Categoria:</span> {{ $goal->category->name }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Progress Info -->
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Progresso</h3>
                            <div class="space-y-4">
                                <!-- Progress Bar -->
                                <div>
                                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                                        <span>Progresso</span>
                                        <span>{{ number_format($progressPercentage, 1) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-4">
                                        <div class="bg-blue-600 h-4 rounded-full transition-all duration-300" 
                                             style="width: {{ min(100, $progressPercentage) }}%"></div>
                                    </div>
                                </div>

                                <!-- Time Info -->
                                <div class="space-y-2">
                                    <div>
                                        <span class="font-medium">Tempo Restante:</span>
                                        <span class="
                                            @if($urgencyLevel === 'overdue') text-red-600
                                            @elseif($urgencyLevel === 'critical') text-red-500
                                            @elseif($urgencyLevel === 'high') text-orange-500
                                            @elseif($urgencyLevel === 'medium') text-yellow-500
                                            @else text-green-500 @endif
                                        ">
                                            {{ $timeRemaining }}
                                        </span>
                                    </div>
                                    @if($daysRemaining > 0)
                                        <div>
                                            <span class="font-medium">Dias Restantes:</span> {{ $daysRemaining }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Update Progress Button -->
                                <form method="POST" action="{{ route('goals.update-progress', $goal) }}">
                                    @csrf
                                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        Atualizar Progresso
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Achievement Alert -->
            @if($goal->status === 'completed')
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <strong>Parabéns!</strong> Você atingiu sua meta!
                    </div>
                </div>
            @endif

            <!-- Related Transactions -->
            @if($transactions->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-4">Transações Relacionadas (Últimas 10)</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($transactions as $transaction)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $transaction->date->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $transaction->description }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                    @if($transaction->type === 'income') bg-green-100 text-green-800
                                                    @else bg-red-100 text-red-800 @endif">
                                                    {{ $transaction->type === 'income' ? 'Receita' : 'Despesa' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium
                                                @if($transaction->type === 'income') text-green-600
                                                @else text-red-600 @endif">
                                                {{ $transaction->type === 'income' ? '+' : '-' }}R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>