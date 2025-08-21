<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Relatórios Financeiros') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filtros -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Filtros</h3>
                    
                    <form method="POST" action="{{ route('reports.generate') }}" class="space-y-4">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Data Inicial -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Data Inicial</label>
                                <input type="date" 
                                       id="start_date" 
                                       name="start_date" 
                                       value="{{ $filters['start_date'] ?? '' }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Data Final -->
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">Data Final</label>
                                <input type="date" 
                                       id="end_date" 
                                       name="end_date" 
                                       value="{{ $filters['end_date'] ?? '' }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <!-- Categoria -->
                            <div>
                                <label for="category_id" class="block text-sm font-medium text-gray-700">Categoria</label>
                                <select id="category_id" 
                                        name="category_id" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Todas as categorias</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                                {{ ($filters['category_id'] ?? '') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tipo -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Tipo</label>
                                <select id="type" 
                                        name="type" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Todos os tipos</option>
                                    <option value="income" {{ ($filters['type'] ?? '') == 'income' ? 'selected' : '' }}>
                                        Receitas
                                    </option>
                                    <option value="expense" {{ ($filters['type'] ?? '') == 'expense' ? 'selected' : '' }}>
                                        Despesas
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-between items-center">
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Gerar Relatório
                            </button>

                            @if($reportData && $reportData['has_data'])
                                <div class="flex space-x-2">
                                    <button type="button" 
                                            onclick="exportReport('excel')"
                                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        Exportar Excel
                                    </button>
                                    <button type="button" 
                                            onclick="exportReport('csv')"
                                            class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                        Exportar CSV
                                    </button>
                                    <button type="button" 
                                            onclick="exportReport('pdf')"
                                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                        Exportar PDF
                                    </button>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            @if($reportData)
                @if($reportData['has_data'])
                    <!-- Resumo -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-medium mb-4">Resumo - {{ $reportData['period'] }}</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <div class="text-sm font-medium text-green-600">Total de Receitas</div>
                                    <div class="text-2xl font-bold text-green-700">
                                        R$ {{ number_format($reportData['totals']['income'], 2, ',', '.') }}
                                    </div>
                                </div>
                                
                                <div class="bg-red-50 p-4 rounded-lg">
                                    <div class="text-sm font-medium text-red-600">Total de Despesas</div>
                                    <div class="text-2xl font-bold text-red-700">
                                        R$ {{ number_format($reportData['totals']['expenses'], 2, ',', '.') }}
                                    </div>
                                </div>
                                
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <div class="text-sm font-medium text-blue-600">Saldo Líquido</div>
                                    <div class="text-2xl font-bold {{ $reportData['totals']['net'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                                        R$ {{ number_format($reportData['totals']['net'], 2, ',', '.') }}
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="text-sm font-medium text-gray-600">Total de Transações</div>
                                    <div class="text-2xl font-bold text-gray-700">
                                        {{ $reportData['totals']['count'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Totais por Categoria -->
                    @if($reportData['category_totals']->isNotEmpty())
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                            <div class="p-6 text-gray-900">
                                <h3 class="text-lg font-medium mb-4">Totais por Categoria</h3>
                                
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Categoria
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Receitas
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Despesas
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Saldo
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Transações
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($reportData['category_totals'] as $categoryTotal)
                                                <tr>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {{ $categoryTotal['category']->name }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                                        R$ {{ number_format($categoryTotal['income'], 2, ',', '.') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">
                                                        R$ {{ number_format($categoryTotal['expenses'], 2, ',', '.') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $categoryTotal['net'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                        R$ {{ number_format($categoryTotal['net'], 2, ',', '.') }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ $categoryTotal['count'] }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Lista de Transações -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-medium mb-4">Transações</h3>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Data
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Descrição
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Categoria
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tipo
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Valor
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($reportData['transactions'] as $transaction)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $transaction->date->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $transaction->description }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $transaction->category->name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                        {{ $transaction->type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        {{ $transaction->type === 'income' ? 'Receita' : 'Despesa' }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $transaction->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                                    R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Mensagem quando não há dados -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <div class="text-center py-8">
                                <div class="text-gray-400 text-6xl mb-4">📊</div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma transação encontrada</h3>
                                <p class="text-gray-500">
                                    Não foram encontradas transações para os filtros selecionados.
                                    Tente ajustar os filtros ou 
                                    <a href="{{ route('transactions.create') }}" class="text-blue-600 hover:text-blue-500">
                                        adicionar uma nova transação
                                    </a>.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <!-- Mensagem inicial -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="text-center py-8">
                            <div class="text-gray-400 text-6xl mb-4">📈</div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Relatórios Financeiros</h3>
                            <p class="text-gray-500">
                                Use os filtros acima para gerar relatórios detalhados das suas transações.
                                Você pode filtrar por período, categoria e tipo de transação.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Form para exportação -->
    <form id="exportForm" method="POST" action="{{ route('reports.export') }}" style="display: none;">
        @csrf
        <input type="hidden" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
        <input type="hidden" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
        <input type="hidden" name="category_id" value="{{ $filters['category_id'] ?? '' }}">
        <input type="hidden" name="type" value="{{ $filters['type'] ?? '' }}">
        <input type="hidden" name="format" id="exportFormat">
    </form>

    <script>
        function exportReport(format) {
            document.getElementById('exportFormat').value = format;
            document.getElementById('exportForm').submit();
        }
    </script>
</x-app-layout>