<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_dashboard_requires_authentication()
    {
        $response = $this->get('/dashboard');
        
        $response->assertRedirect('/login');
    }

    public function test_dashboard_displays_welcome_message_for_new_users()
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Bem-vindo ao Sistema de Gerenciamento Financeiro!');
        $response->assertSee('Você ainda não possui transações registradas');
        $response->assertSee('Criar Categoria');
        $response->assertSee('Registrar Transação');
        $response->assertDontSee('Saldo Atual');
    }

    public function test_dashboard_displays_financial_summary_with_transactions()
    {
        $this->createSampleTransactions();
        
        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Saldo Atual');
        $response->assertSee('Receitas do Mês');
        $response->assertSee('Despesas do Mês');
        $response->assertSee('Resultado do Mês');
        $response->assertDontSee('Bem-vindo ao Sistema de Gerenciamento Financeiro!');
    }

    public function test_dashboard_displays_current_balance_correctly()
    {
        $incomeCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income'
        ]);
        
        $expenseCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense'
        ]);

        // Create transactions with known values
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 1000,
            'type' => 'income',
            'date' => Carbon::now()->subMonth()
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 300,
            'type' => 'expense',
            'date' => Carbon::now()->subMonth()
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 500,
            'type' => 'income',
            'date' => Carbon::now()
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        // Balance should be: 1000 - 300 + 500 = 1200
        $response->assertSee('R$ 1.200,00');
    }

    public function test_dashboard_displays_monthly_totals_correctly()
    {
        $incomeCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income'
        ]);
        
        $expenseCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense'
        ]);

        // Current month transactions
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 800,
            'type' => 'income',
            'date' => Carbon::now()->startOfMonth()->addDays(5)
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 250,
            'type' => 'expense',
            'date' => Carbon::now()->startOfMonth()->addDays(10)
        ]);

        // Previous month transaction (should not affect monthly totals)
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 1000,
            'type' => 'income',
            'date' => Carbon::now()->subMonth()
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('R$ 800,00'); // Monthly income
        $response->assertSee('R$ 250,00'); // Monthly expenses
        $response->assertSee('R$ 550,00'); // Monthly net (800 - 250)
    }

    public function test_dashboard_displays_recent_transactions()
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income',
            'name' => 'Salary'
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'amount' => 500,
            'type' => 'income',
            'date' => Carbon::now(),
            'description' => 'Test Transaction'
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Últimas Transações');
        $response->assertSee('Test Transaction');
        $response->assertSee('Salary');
        $response->assertSee('Receita');
        $response->assertSee('Ver Todas');
    }

    public function test_dashboard_displays_category_summary()
    {
        $incomeCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income',
            'name' => 'Freelance'
        ]);
        
        $expenseCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'name' => 'Food'
        ]);

        // Current month transactions
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 600,
            'type' => 'income',
            'date' => Carbon::now()->startOfMonth()->addDays(5)
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 150,
            'type' => 'expense',
            'date' => Carbon::now()->startOfMonth()->addDays(10)
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Resumo por Categoria');
        $response->assertSee('Freelance');
        $response->assertSee('Food');
        $response->assertSee('R$ 600,00');
        $response->assertSee('R$ 150,00');
    }

    public function test_dashboard_includes_chart_data_for_users_with_transactions()
    {
        $this->createSampleTransactions();
        
        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('financialChart');
        $response->assertSee('Evolução Financeira (6 meses)');
        // Check that Chart.js script is included
        $response->assertSee('new Chart(ctx');
    }

    public function test_dashboard_does_not_include_chart_for_users_without_transactions()
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertDontSee('financialChart');
        $response->assertDontSee('new Chart(ctx');
    }

    public function test_dashboard_shows_positive_monthly_result_correctly()
    {
        $incomeCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income'
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 1000,
            'type' => 'income',
            'date' => Carbon::now()
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('bg-info'); // Positive result should use info color
        $response->assertSee('fa-chart-line'); // Positive result icon
    }

    public function test_dashboard_shows_negative_monthly_result_correctly()
    {
        $expenseCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense'
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 500,
            'type' => 'expense',
            'date' => Carbon::now()
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('bg-warning'); // Negative result should use warning color
        $response->assertSee('fa-exclamation-triangle'); // Negative result icon
    }

    public function test_dashboard_only_shows_user_own_data()
    {
        $otherUser = User::factory()->create();
        
        $userCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income'
        ]);
        
        $otherUserCategory = Category::factory()->create([
            'user_id' => $otherUser->id,
            'type' => 'income'
        ]);

        // User's transaction
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $userCategory->id,
            'amount' => 500,
            'type' => 'income',
            'date' => Carbon::now(),
            'description' => 'User Transaction'
        ]);

        // Other user's transaction
        Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $otherUserCategory->id,
            'amount' => 1000,
            'type' => 'income',
            'date' => Carbon::now(),
            'description' => 'Other User Transaction'
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('User Transaction');
        $response->assertDontSee('Other User Transaction');
        $response->assertSee('R$ 500,00'); // User's balance
        $response->assertDontSee('R$ 1.000,00'); // Other user's amount
    }

    // Requisito 4.1: Exibir saldo atual, total de receitas e despesas do mês
    public function test_dashboard_displays_all_required_financial_metrics()
    {
        $this->createSampleTransactions();
        
        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Saldo Atual');
        $response->assertSee('Receitas do Mês');
        $response->assertSee('Despesas do Mês');
        $response->assertSee('Resultado do Mês');
        
        // Verify that all metrics are displayed with proper formatting
        $response->assertSee('R$');
    }

    // Requisito 4.2: Mostrar as últimas 5 transações registradas
    public function test_dashboard_displays_last_five_transactions()
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        
        // Create 7 transactions to test the limit
        for ($i = 1; $i <= 7; $i++) {
            Transaction::factory()->create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'description' => "Transaction {$i}",
                'date' => Carbon::now()->subDays($i),
            ]);
        }

        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Últimas Transações');
        
        // Should see the 5 most recent transactions
        $response->assertSee('Transaction 1');
        $response->assertSee('Transaction 2');
        $response->assertSee('Transaction 3');
        $response->assertSee('Transaction 4');
        $response->assertSee('Transaction 5');
        
        // Should not see the older transactions
        $response->assertDontSee('Transaction 6');
        $response->assertDontSee('Transaction 7');
    }

    // Requisito 4.3: Exibir gráfico com evolução do saldo nos últimos 6 meses
    public function test_dashboard_displays_six_month_evolution_chart()
    {
        $this->createSampleTransactions();
        
        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Evolução Financeira (6 meses)');
        $response->assertSee('financialChart');
        
        // Verify chart data structure
        $response->assertSee('chartData');
        $response->assertSee('labels');
        $response->assertSee('datasets');
    }

    // Requisito 4.4: Mostrar resumo por categorias do mês atual
    public function test_dashboard_displays_current_month_category_summary()
    {
        $incomeCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income',
            'name' => 'Salary'
        ]);
        
        $expenseCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'name' => 'Food'
        ]);

        // Current month transactions
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 3000,
            'type' => 'income',
            'date' => Carbon::now()->startOfMonth()->addDays(5)
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 500,
            'type' => 'expense',
            'date' => Carbon::now()->startOfMonth()->addDays(10)
        ]);

        // Previous month transaction (should not appear in current month summary)
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 2000,
            'type' => 'income',
            'date' => Carbon::now()->subMonth()
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Resumo por Categoria');
        $response->assertSee('Salary');
        $response->assertSee('Food');
        $response->assertSee('R$ 3.000,00'); // Current month salary
        $response->assertSee('R$ 500,00'); // Current month food
        // Note: Previous month transaction affects total balance but not monthly summary
    }

    // Requisito 4.5: Exibir mensagem orientativa para usuários sem transações
    public function test_dashboard_shows_guidance_message_for_users_without_transactions()
    {
        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Bem-vindo ao Sistema de Gerenciamento Financeiro!');
        $response->assertSee('Você ainda não possui transações registradas');
        $response->assertSee('Criar Categoria');
        $response->assertSee('Registrar Transação');
        
        // Should not show financial metrics when no transactions
        $response->assertDontSee('Saldo Atual');
        $response->assertDontSee('Receitas do Mês');
        $response->assertDontSee('Despesas do Mês');
    }

    // Teste adicional: Verificar cálculos corretos de saldo
    public function test_dashboard_calculates_balance_correctly_across_multiple_months()
    {
        $incomeCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income'
        ]);
        
        $expenseCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense'
        ]);

        // Transactions across different months
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 2000,
            'type' => 'income',
            'date' => Carbon::now()->subMonths(2)
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 500,
            'type' => 'expense',
            'date' => Carbon::now()->subMonth()
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 1000,
            'type' => 'income',
            'date' => Carbon::now()
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        // Total balance: 2000 - 500 + 1000 = 2500
        $response->assertSee('R$ 2.500,00');
    }

    // Teste adicional: Verificar que apenas dados do usuário são exibidos
    public function test_dashboard_isolates_user_data_properly()
    {
        $otherUser = User::factory()->create();
        
        // Create categories for both users
        $userCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'User Category'
        ]);
        
        $otherCategory = Category::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'Other Category'
        ]);

        // Create transactions for both users
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $userCategory->id,
            'amount' => 100,
            'description' => 'User Transaction'
        ]);

        Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $otherCategory->id,
            'amount' => 999,
            'description' => 'Other Transaction'
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('User Transaction');
        $response->assertSee('User Category');
        $response->assertDontSee('Other Transaction');
        $response->assertDontSee('Other Category');
        $response->assertDontSee('R$ 999,00');
    }

    private function createSampleTransactions(): void
    {
        $incomeCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income'
        ]);
        
        $expenseCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense'
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 1000,
            'type' => 'income',
            'date' => Carbon::now()
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 300,
            'type' => 'expense',
            'date' => Carbon::now()
        ]);
    }
}