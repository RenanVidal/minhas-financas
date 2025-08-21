<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Categoria: ') . $category->name }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('categories.edit', $category) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                    Editar
                </a>
                <a href="{{ route('categories.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Informações da Categoria</h3>
                            
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 rounded-full mr-3" style="background-color: {{ $category->color }}"></div>
                                    <span class="font-medium">{{ $category->name }}</span>
                                </div>
                                
                                <div>
                                    <span class="text-gray-600">Tipo:</span>
                                    <span class="ml-2 px-2 py-1 text-xs rounded-full {{ $category->type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $category->type === 'income' ? 'Receita' : 'Despesa' }}
                                    </span>
                                </div>
                                
                                @if($category->description)
                                    <div>
                                        <span class="text-gray-600">Descrição:</span>
                                        <p class="mt-1 text-gray-900">{{ $category->description }}</p>
                                    </div>
                                @endif
                                
                                <div>
                                    <span class="text-gray-600">Criada em:</span>
                                    <span class="ml-2">{{ $category->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Estatísticas</h3>
                            
                            <div class="space-y-3">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-gray-900">{{ $transactionsCount }}</div>
                                    <div class="text-sm text-gray-600">Transações</div>
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold {{ $category->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                        R$ {{ number_format($totalAmount, 2, ',', '.') }}
                                    </div>
                                    <div class="text-sm text-gray-600">Total Movimentado</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($transactionsCount > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold">Transações Recentes</h3>
                            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm">Ver todas</a>
                        </div>
                        
                        <div class="text-center py-8 text-gray-500">
                            <p>As transações serão exibidas aqui quando o módulo de transações for implementado.</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="text-center py-8">
                            <div class="text-gray-500 mb-4">
                                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma transação encontrada</h3>
                            <p class="text-gray-500 mb-4">Esta categoria ainda não possui transações associadas.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>