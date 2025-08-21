<x-app-layout>
    <x-slot name="header">
        <h2 class="h3 mb-0">
            {{ __('Dashboard Financeiro') }}
        </h2>
    </x-slot>

    <!-- Goal Notifications -->
    @if(isset($recentAchievements) && $recentAchievements->count() > 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading">🎉 Parabéns! Você atingiu {{ $recentAchievements->count() }} meta(s)!</h5>
                    @foreach($recentAchievements as $achievement)
                        <p class="mb-1"><strong>{{ $achievement->name }}</strong> - R$ {{ number_format($achievement->target_amount, 2, ',', '.') }}</p>
                    @endforeach
                    <hr>
                    <a href="{{ route('goals.achievements') }}" class="btn btn-sm btn-success">Ver Todas as Conquistas</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if(isset($overdueGoals) && $overdueGoals->count() > 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h6 class="alert-heading">⚠️ Metas Vencidas</h6>
                    <p>Você tem {{ $overdueGoals->count() }} meta(s) que já passaram do prazo:</p>
                    @foreach($overdueGoals->take(3) as $goal)
                        <p class="mb-1">• <strong>{{ $goal->name }}</strong> - Venceu em {{ $goal->deadline->format('d/m/Y') }}</p>
                    @endforeach
                    @if($overdueGoals->count() > 3)
                        <p class="mb-1">... e mais {{ $overdueGoals->count() - 3 }} meta(s)</p>
                    @endif
                    <hr>
                    <a href="{{ route('goals.index') }}" class="btn btn-sm btn-danger">Gerenciar Metas</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if(isset($expiringGoals) && $expiringGoals->count() > 0)
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <h6 class="alert-heading">⏰ Metas Próximas do Vencimento</h6>
                    <p>{{ $expiringGoals->count() }} meta(s) vencem esta semana:</p>
                    @foreach($expiringGoals->take(3) as $goal)
                        <p class="mb-1">• <strong>{{ $goal->name }}</strong> - Vence em {{ $goal->deadline->format('d/m/Y') }}</p>
                    @endforeach
                    @if($expiringGoals->count() > 3)
                        <p class="mb-1">... e mais {{ $expiringGoals->count() - 3 }} meta(s)</p>
                    @endif
                    <hr>
                    <a href="{{ route('goals.index') }}" class="btn btn-sm btn-warning">Ver Metas</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if(!$dashboardData['has_transactions'])
        <!-- Welcome message for new users -->
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info" role="alert">
                    <h4 class="alert-heading">{{ __('Bem-vindo ao Sistema de Gerenciamento Financeiro!') }}</h4>
                    <p>{{ __('Você ainda não possui transações registradas. Comece criando algumas categorias e registrando suas receitas e despesas para ter uma visão completa das suas finanças.') }}</p>
                    <hr>
                    <div class="d-flex gap-2">
                        <a href="{{ route('categories.create') }}" class="btn btn-primary">
                            {{ __('Criar Categoria') }}
                        </a>
                        <a href="{{ route('transactions.create') }}" class="btn btn-success">
                            {{ __('Registrar Transação') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Financial Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">{{ __('Saldo Atual') }}</h6>
                                <h4 class="mb-0">R$ {{ number_format($dashboardData['current_balance'], 2, ',', '.') }}</h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-wallet fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">{{ __('Receitas do Mês') }}</h6>
                                <h4 class="mb-0">R$ {{ number_format($dashboardData['monthly_income'], 2, ',', '.') }}</h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-arrow-up fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">{{ __('Despesas do Mês') }}</h6>
                                <h4 class="mb-0">R$ {{ number_format($dashboardData['monthly_expenses'], 2, ',', '.') }}</h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-arrow-down fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card text-white {{ $dashboardData['monthly_net'] >= 0 ? 'bg-info' : 'bg-warning' }}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">{{ __('Resultado do Mês') }}</h6>
                                <h4 class="mb-0">R$ {{ number_format($dashboardData['monthly_net'], 2, ',', '.') }}</h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-{{ $dashboardData['monthly_net'] >= 0 ? 'chart-line' : 'exclamation-triangle' }} fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Financial Chart -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Evolução Financeira (6 meses)') }}</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="financialChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Category Summary -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Resumo por Categoria') }}</h5>
                        <small class="text-muted">{{ __('Mês atual') }}</small>
                    </div>
                    <div class="card-body">
                        @if($dashboardData['category_summary']->isEmpty())
                            <p class="text-muted">{{ __('Nenhuma transação neste mês.') }}</p>
                        @else
                            @foreach($dashboardData['category_summary'] as $summary)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <span class="badge bg-{{ $summary['type'] === 'income' ? 'success' : 'danger' }} me-2">
                                            {{ $summary['type'] === 'income' ? 'R' : 'D' }}
                                        </span>
                                        <strong>{{ $summary['category']->name }}</strong>
                                        <small class="text-muted">({{ $summary['count'] }})</small>
                                    </div>
                                    <span class="text-{{ $summary['type'] === 'income' ? 'success' : 'danger' }}">
                                        R$ {{ number_format($summary['total'], 2, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{ __('Últimas Transações') }}</h5>
                        <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-primary">
                            {{ __('Ver Todas') }}
                        </a>
                    </div>
                    <div class="card-body">
                        @if($dashboardData['recent_transactions']->isEmpty())
                            <p class="text-muted">{{ __('Nenhuma transação encontrada.') }}</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Data') }}</th>
                                            <th>{{ __('Descrição') }}</th>
                                            <th>{{ __('Categoria') }}</th>
                                            <th>{{ __('Tipo') }}</th>
                                            <th class="text-end">{{ __('Valor') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dashboardData['recent_transactions'] as $transaction)
                                            <tr>
                                                <td>{{ $transaction->date->format('d/m/Y') }}</td>
                                                <td>{{ $transaction->description }}</td>
                                                <td>{{ $transaction->category->name }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $transaction->type === 'income' ? 'success' : 'danger' }}">
                                                        {{ $transaction->type === 'income' ? 'Receita' : 'Despesa' }}
                                                    </span>
                                                </td>
                                                <td class="text-end text-{{ $transaction->type === 'income' ? 'success' : 'danger' }}">
                                                    R$ {{ number_format($transaction->amount, 2, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($dashboardData['has_transactions'])
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('financialChart').getContext('2d');
                const chartData = @json($chartData);
                
                const chart = new Chart(ctx, {
                    type: 'line',
                    data: chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Evolução Financeira'
                            },
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: false,
                                ticks: {
                                    callback: function(value, index, values) {
                                        return 'R$ ' + value.toLocaleString('pt-BR', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        });
                                    }
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        }
                    }
                });
            });
        </script>
    @endif
</x-app-layout>
