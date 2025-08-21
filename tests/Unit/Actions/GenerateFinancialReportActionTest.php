<?php

namespace Tests\Unit\Actions;

use App\Actions\GenerateFinancialReportAction;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateFinancialReportActionTest extends TestCase
{
    use RefreshDatabase;

    private GenerateFinancialReportAction $action;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new GenerateFinancialReportAction();
        $this->user = User::factory()->create();
    }

    public function test_execute_returns_empty_report_when_no_transactions()
    {
        $result = $this->action->execute($this->user);

        $this->assertFalse($result['has_data']);
        $this->assertEmpty($result['transactions']);
        $this->assertEquals(0, $result['totals']['count']);
        $this->assertEquals(0, $result['totals']['income']);
        $this->assertEquals(0, $result['totals']['expenses']);
        $this->assertEquals(0, $result['totals']['net']);
    }

    public function test_execute_returns_all_transactions_without_filters()
    {
        $category1 = Category::factory()->create(['user_id' => $this->user->id, 'type' => 'income']);
        $category2 = Category::factory()->create(['user_id' => $this->user->id, 'type' => 'expense']);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category1->id,
            'type' => 'income',
            'amount' => 1000,
            'date' => '2024-01-15'
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category2->id,
            'type' => 'expense',
            'amount' => 500,
            'date' => '2024-01-20'
        ]);

        $result = $this->action->execute($this->user);

        $this->assertTrue($result['has_data']);
        $this->assertCount(2, $result['transactions']);
        $this->assertEquals(2, $result['totals']['count']);
        $this->assertEquals(1000, $result['totals']['income']);
        $this->assertEquals(500, $result['totals']['expenses']);
        $this->assertEquals(500, $result['totals']['net']);
    }

    public function test_execute_filters_by_date_range()
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        // Transaction within range
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'amount' => 1000,
            'date' => '2024-01-15'
        ]);

        // Transaction outside range
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'amount' => 500,
            'date' => '2024-02-15'
        ]);

        $filters = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31'
        ];

        $result = $this->action->execute($this->user, $filters);

        $this->assertCount(1, $result['transactions']);
        $this->assertEquals(1000, $result['transactions']->first()->amount);
        $this->assertEquals('01/01/2024 - 31/01/2024', $result['period']);
    }

    public function test_execute_filters_by_category()
    {
        $category1 = Category::factory()->create(['user_id' => $this->user->id]);
        $category2 = Category::factory()->create(['user_id' => $this->user->id]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category1->id,
            'amount' => 1000
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category2->id,
            'amount' => 500
        ]);

        $filters = ['category_id' => $category1->id];
        $result = $this->action->execute($this->user, $filters);

        $this->assertCount(1, $result['transactions']);
        $this->assertEquals($category1->id, $result['transactions']->first()->category_id);
    }

    public function test_execute_filters_by_transaction_type()
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'type' => 'income',
            'amount' => 1000
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 500
        ]);

        $filters = ['type' => 'income'];
        $result = $this->action->execute($this->user, $filters);

        $this->assertCount(1, $result['transactions']);
        $this->assertEquals('income', $result['transactions']->first()->type);
    }

    public function test_execute_calculates_category_totals_correctly()
    {
        $category1 = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Salário']);
        $category2 = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Alimentação']);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category1->id,
            'type' => 'income',
            'amount' => 1000
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category1->id,
            'type' => 'income',
            'amount' => 500
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category2->id,
            'type' => 'expense',
            'amount' => 300
        ]);

        $result = $this->action->execute($this->user);

        $this->assertCount(2, $result['category_totals']);
        
        $category1Total = $result['category_totals']->firstWhere('category.id', $category1->id);
        $this->assertEquals(1500, $category1Total['income']);
        $this->assertEquals(0, $category1Total['expenses']);
        $this->assertEquals(1500, $category1Total['total']);
        $this->assertEquals(2, $category1Total['count']);

        $category2Total = $result['category_totals']->firstWhere('category.id', $category2->id);
        $this->assertEquals(0, $category2Total['income']);
        $this->assertEquals(300, $category2Total['expenses']);
        $this->assertEquals(300, $category2Total['total']);
        $this->assertEquals(1, $category2Total['count']);
    }

    public function test_execute_calculates_type_totals_correctly()
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'type' => 'income',
            'amount' => 1000
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'type' => 'income',
            'amount' => 500
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 300
        ]);

        $result = $this->action->execute($this->user);

        $this->assertEquals(1500, $result['type_totals']['income']['total']);
        $this->assertEquals(2, $result['type_totals']['income']['count']);
        $this->assertEquals(750, $result['type_totals']['income']['average']);

        $this->assertEquals(300, $result['type_totals']['expense']['total']);
        $this->assertEquals(1, $result['type_totals']['expense']['count']);
        $this->assertEquals(300, $result['type_totals']['expense']['average']);
    }

    public function test_execute_orders_transactions_by_date_desc()
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $transaction1 = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'date' => '2024-01-10'
        ]);

        $transaction2 = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'date' => '2024-01-20'
        ]);

        $transaction3 = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'date' => '2024-01-15'
        ]);

        $result = $this->action->execute($this->user);

        $this->assertEquals($transaction2->id, $result['transactions'][0]->id);
        $this->assertEquals($transaction3->id, $result['transactions'][1]->id);
        $this->assertEquals($transaction1->id, $result['transactions'][2]->id);
    }

    public function test_get_period_description_formats_correctly()
    {
        // Test with both dates
        $filters = [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31'
        ];
        $result = $this->action->execute($this->user, $filters);
        $this->assertEquals('01/01/2024 - 31/01/2024', $result['period']);

        // Test with start date only
        $filters = ['start_date' => '2024-01-01'];
        $result = $this->action->execute($this->user, $filters);
        $this->assertEquals('A partir de 01/01/2024', $result['period']);

        // Test with end date only
        $filters = ['end_date' => '2024-01-31'];
        $result = $this->action->execute($this->user, $filters);
        $this->assertEquals('Até 31/01/2024', $result['period']);

        // Test with no dates
        $result = $this->action->execute($this->user);
        $this->assertEquals('Todos os períodos', $result['period']);
    }
}