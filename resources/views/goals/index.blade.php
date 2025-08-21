<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Metas Financeiras') }}
            </h2>
            <a href="{{ route('goals.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Nova Meta
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Achievement Stats -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Estatísticas de Metas</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ $stats['total'] }}</div>
                            <div class="text-sm text-gray-600">Total de Metas</div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">{{ $stats['completed'] }}</div>
                            <div class="text-sm text-gray-600">Concluídas</div>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-yellow-600">{{ $stats['active'] }}</div>
                            <div class="text-sm text-gray-600">Ativas</div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600">{{ $stats['completion_rate'] }}%</div>
                            <div class="text-sm text-gray-600">Taxa de Sucesso</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            @if($overdueGoals->count() > 0)
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <strong>Atenção!</strong> Você tem {{ $overdueGoals->count() }} meta(s) vencida(s).
                </div>
            @endif

            @if($expiringGoals->count() > 0)
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                    <strong>Aviso!</strong> Você tem {{ $expiringGoals->count() }} meta(s) vencendo esta semana.
                </div>
            @endif

            <!-- Goals List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($goals->count() > 0)
                        <div class="space-y-4">
                            @foreach($goals as $goal)
                                <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 class="text-lg font-semibold">
                                                <a href="{{ route('goals.show', $goal) }}" class="text-blue-600 hover:text-blue-800">
                                                    {{ $goal->name }}
                                                </a>
                                            </h4>
                                            @if($goal->category)
                                                <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mt-1">
                                                    {{ $goal->category->name }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm text-gray-600">
                                                R$ {{ number_format($goal->current_amount, 2, ',', '.') }} / R$ {{ number_format($goal->target_amount, 2, ',', '.') }}
                                            </div>
                                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full
                                                @if($goal->status === 'completed') bg-green-100 text-green-800
                                                @elseif($goal->status === 'cancelled') bg-red-100 text-red-800
                                                @else bg-blue-100 text-blue-800 @endif">
                                                @if($goal->status === 'completed') Concluída
                                                @elseif($goal->status === 'cancelled') Cancelada
                                                @else Ativa @endif
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Progress Bar -->
                                    <div class="mb-3">
                                        @php
                                            $percentage = min(100, ($goal->current_amount / $goal->target_amount) * 100);
                                        @endphp
                                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                                            <span>Progresso</span>
                                            <span>{{ number_format($percentage, 1) }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>

                                    <div class="flex justify-between items-center text-sm text-gray-600">
                                        <div>
                                            Prazo: {{ $goal->deadline->format('d/m/Y') }}
                                            @if($goal->deadline->isPast() && $goal->status === 'active')
                                                <span class="text-red-600 font-semibold">(Vencida)</span>
                                            @elseif($goal->deadline->diffInDays(now()) <= 7 && $goal->deadline->isFuture())
                                                <span class="text-yellow-600 font-semibold">({{ $goal->deadline->diffInDays(now()) }} dias restantes)</span>
                                            @endif
                                        </div>
                                        <div class="space-x-2">
                                            <a href="{{ route('goals.show', $goal) }}" class="text-blue-600 hover:text-blue-800">Ver</a>
                                            <a href="{{ route('goals.edit', $goal) }}" class="text-green-600 hover:text-green-800">Editar</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="text-gray-500 mb-4">
                                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma meta encontrada</h3>
                            <p class="text-gray-500 mb-4">Comece criando sua primeira meta financeira!</p>
                            <a href="{{ route('goals.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Criar Primeira Meta
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>