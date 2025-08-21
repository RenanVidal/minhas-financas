<?php

namespace Tests\Unit\Actions;

use App\Actions\GenerateFinancialChartAction;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateFinancialChartActionTest extends TestCase
{
    use RefreshDatabase;

    private GenerateFinancialChartAction $action;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new GenerateFinancialChartAction();
        $this->user = User::factory()->create();
    }

    public function test_generates_chart_data_with_no_transactions()
    {
        $result = $this->action->execute($this->user, 3);

        $this->assertCount(3, $result['labels']);
        $this->assertCount(3, $result['datasets']);
        
        // Check that all datasets have 3 months of data
        foreach ($result['datasets'] as $dataset) {
            $this->assertCount(3, $dataset['data']);
            // All values should be 0 when no transactions
            $this->assertEquals([0, 0, 0], $dataset['data']);
        }
    }

    public function test_generates_correct_labels_for_months()
    {
        Carbon::setTestNow('2024-03-15'); // Set a fixed date for testing
        
        $result = $this->action->execute($this->user, 3);

        $expectedLabels = [
            Carbon::now()->subMonths(2)->format('M/Y'), // Jan/24
            Carbon::now()->subMonths(1)->format('M/Y'), // Feb/24
            Carbon::now()->format('M/Y'),               // Mar/24
        ];

        $this->assertEquals($expectedLabels, $result['labels']);
        
        Carbon::setTestNow(); // Reset
    }

    public function test_calculates_monthly_income_and_expenses_correctly()
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
            'amount' => 1000,
            'type' => 'income',
            'date' => Carbon::now()->startOfMonth()->addDays(5)
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 300,
            'type' => 'expense',
            'date' => Carbon::now()->startOfMonth()->addDays(10)
        ]);

        // Previous month transactions
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 800,
            'type' => 'income',
            'date' => Carbon::now()->subMonth()->startOfMonth()->addDays(5)
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 200,
            'type' => 'expense',
            'date' => Carbon::now()->subMonth()->startOfMonth()->addDays(10)
        ]);

        $result = $this->action->execute($this->user, 2);

        // Check income dataset (last month, current month)
        $incomeData = $result['datasets'][0]['data'];
        $this->assertEquals([800, 1000], $incomeData);

        // Check expenses dataset
        $expensesData = $result['datasets'][1]['data'];
        $this->assertEquals([200, 300], $expensesData);
    }

    public function test_calculates_running_balance_correctly()
    {
        $incomeCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income'
        ]);
        
        $expenseCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense'
        ]);

        // Month 1: +500 (1000 income - 500 expense)
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 1000,
            'type' => 'income',
            'date' => Carbon::now()->subMonths(2)->startOfMonth()->addDays(5)
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 500,
            'type' => 'expense',
            'date' => Carbon::now()->subMonths(2)->startOfMonth()->addDays(10)
        ]);

        // Month 2: +300 (800 income - 500 expense)
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 800,
            'type' => 'income',
            'date' => Carbon::now()->subMonth()->startOfMonth()->addDays(5)
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 500,
            'type' => 'expense',
            'date' => Carbon::now()->subMonth()->startOfMonth()->addDays(10)
        ]);

        // Month 3: -200 (200 income - 400 expense)
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 200,
            'type' => 'income',
            'date' => Carbon::now()->startOfMonth()->addDays(5)
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 400,
            'type' => 'expense',
            'date' => Carbon::now()->startOfMonth()->addDays(10)
        ]);

        $result = $this->action->execute($this->user, 3);

        // Check balance dataset - should be cumulative
        $balanceData = $result['datasets'][2]['data'];
        $this->assertEquals([500, 800, 600], $balanceData); // 500, 500+300, 800-200
    }

    public function test_generates_balance_only_chart()
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
            'date' => Carbon::now()->startOfMonth()->addDays(5)
        ]);

        $result = $this->action->executeBalanceOnly($this->user, 2);

        $this->assertCount(1, $result['datasets']);
        $this->assertEquals('Evolução do Saldo', $result['datasets'][0]['label']);
        $this->assertTrue($result['datasets'][0]['fill']);
        $this->assertEquals(0.4, $result['datasets'][0]['tension']);
    }

    public function test_returns_raw_data_correctly()
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
            'date' => Carbon::now()->startOfMonth()->addDays(5)
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 300,
            'type' => 'expense',
            'date' => Carbon::now()->startOfMonth()->addDays(10)
        ]);

        $result = $this->action->getRawData($this->user, 1);

        $this->assertCount(1, $result);
        $monthData = $result->first();
        
        $this->assertArrayHasKey('label', $monthData);
        $this->assertArrayHasKey('month', $monthData);
        $this->assertArrayHasKey('income', $monthData);
        $this->assertArrayHasKey('expenses', $monthData);
        $this->assertArrayHasKey('net', $monthData);
        $this->assertArrayHasKey('balance', $monthData);
        
        $this->assertEquals(1000.0, $monthData['income']);
        $this->assertEquals(300.0, $monthData['expenses']);
        $this->assertEquals(700.0, $monthData['net']);
        $this->assertEquals(700.0, $monthData['balance']);
    }

    public function test_only_includes_user_own_transactions()
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
            'date' => Carbon::now()
        ]);

        // Other user's transaction
        Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $otherUserCategory->id,
            'amount' => 1000,
            'type' => 'income',
            'date' => Carbon::now()
        ]);

        $result = $this->action->execute($this->user, 1);

        // Should only include user's data
        $incomeData = $result['datasets'][0]['data'];
        $this->assertEquals([500], $incomeData);
        
        $balanceData = $result['datasets'][2]['data'];
        $this->assertEquals([500], $balanceData);
    }

    public function test_handles_different_month_counts()
    {
        $result1 = $this->action->execute($this->user, 1);
        $result6 = $this->action->execute($this->user, 6);
        $result12 = $this->action->execute($this->user, 12);

        $this->assertCount(1, $result1['labels']);
        $this->assertCount(6, $result6['labels']);
        $this->assertCount(12, $result12['labels']);

        foreach ([$result1, $result6, $result12] as $result) {
            foreach ($result['datasets'] as $dataset) {
                $this->assertCount(count($result['labels']), $dataset['data']);
            }
        }
    }
}