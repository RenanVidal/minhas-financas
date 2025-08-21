<?php

namespace Tests\Unit\Actions;

use App\Actions\CalculateDashboardDataAction;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateDashboardDataActionTest extends TestCase
{
    use RefreshDatabase;

    private CalculateDashboardDataAction $action;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CalculateDashboardDataAction();
        $this->user = User::factory()->create();
    }

    public function test_calculates_dashboard_data_with_no_transactions()
    {
        $result = $this->action->execute($this->user);

        $this->assertEquals(0, $result['current_balance']);
        $this->assertEquals(0, $result['monthly_income']);
        $this->assertEquals(0, $result['monthly_expenses']);
        $this->assertEquals(0, $result['monthly_net']);
        $this->assertEmpty($result['recent_transactions']);
        $this->assertEmpty($result['category_summary']);
        $this->assertFalse($result['has_transactions']);
    }

    public function test_calculates_current_balance_correctly()
    {
        $incomeCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income'
        ]);
        
        $expenseCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense'
        ]);

        // Create transactions from different months
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

        $result = $this->action->execute($this->user);

        // Balance should be: 1000 - 300 + 500 = 1200
        $this->assertEquals(1200, $result['current_balance']);
        $this->assertTrue($result['has_transactions']);
    }

    public function test_calculates_monthly_totals_correctly()
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
            'amount' => 200,
            'type' => 'expense',
            'date' => Carbon::now()->startOfMonth()->addDays(10)
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 150,
            'type' => 'expense',
            'date' => Carbon::now()->startOfMonth()->addDays(15)
        ]);

        // Previous month transaction (should not be included in monthly totals)
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'amount' => 1000,
            'type' => 'income',
            'date' => Carbon::now()->subMonth()
        ]);

        $result = $this->action->execute($this->user);

        $this->assertEquals(800, $result['monthly_income']);
        $this->assertEquals(350, $result['monthly_expenses']); // 200 + 150
        $this->assertEquals(450, $result['monthly_net']); // 800 - 350
    }

    public function test_returns_recent_transactions_ordered_by_date()
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income'
        ]);

        // Create 7 transactions to test the limit of 5
        for ($i = 1; $i <= 7; $i++) {
            Transaction::factory()->create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'amount' => 100 * $i,
                'type' => 'income',
                'date' => Carbon::now()->subDays($i),
                'description' => "Transaction {$i}"
            ]);
        }

        $result = $this->action->execute($this->user);

        $this->assertCount(5, $result['recent_transactions']);
        
        // Should be ordered by date desc (most recent first)
        $transactions = $result['recent_transactions'];
        $this->assertEquals('Transaction 1', $transactions[0]->description);
        $this->assertEquals('Transaction 2', $transactions[1]->description);
        $this->assertEquals('Transaction 5', $transactions[4]->description);
    }

    public function test_generates_category_summary_correctly()
    {
        $incomeCategory1 = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income',
            'name' => 'Salary'
        ]);
        
        $incomeCategory2 = Category::factory()->create([
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
            'category_id' => $incomeCategory1->id,
            'amount' => 3000,
            'type' => 'income',
            'date' => Carbon::now()->startOfMonth()->addDays(1)
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory2->id,
            'amount' => 500,
            'type' => 'income',
            'date' => Carbon::now()->startOfMonth()->addDays(5)
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 200,
            'type' => 'expense',
            'date' => Carbon::now()->startOfMonth()->addDays(10)
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $expenseCategory->id,
            'amount' => 150,
            'type' => 'expense',
            'date' => Carbon::now()->startOfMonth()->addDays(15)
        ]);

        $result = $this->action->execute($this->user);

        $this->assertCount(3, $result['category_summary']);
        
        // Should be ordered by total desc
        $summary = $result['category_summary'];
        $this->assertEquals('Salary', $summary[0]['category']->name);
        $this->assertEquals(3000, $summary[0]['total']);
        $this->assertEquals(1, $summary[0]['count']);
        $this->assertEquals('income', $summary[0]['type']);

        $this->assertEquals('Freelance', $summary[1]['category']->name);
        $this->assertEquals(500, $summary[1]['total']);

        $this->assertEquals('Food', $summary[2]['category']->name);
        $this->assertEquals(350, $summary[2]['total']); // 200 + 150
        $this->assertEquals(2, $summary[2]['count']);
        $this->assertEquals('expense', $summary[2]['type']);
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

        $result = $this->action->execute($this->user);

        $this->assertEquals(500, $result['current_balance']);
        $this->assertEquals(500, $result['monthly_income']);
        $this->assertCount(1, $result['recent_transactions']);
        $this->assertCount(1, $result['category_summary']);
    }
}