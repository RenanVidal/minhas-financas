<?php

namespace Tests\Unit\Actions;

use App\Actions\CreateTransactionAction;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTransactionActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateTransactionAction $action;
    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->action = new CreateTransactionAction();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_creates_transaction_with_valid_data(): void
    {
        $data = [
            'category_id' => $this->category->id,
            'description' => 'Test transaction',
            'amount' => 100.50,
            'type' => 'income',
            'date' => '2025-01-15',
        ];

        $transaction = $this->action->execute($this->user, $data);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals($this->user->id, $transaction->user_id);
        $this->assertEquals($data['category_id'], $transaction->category_id);
        $this->assertEquals($data['description'], $transaction->description);
        $this->assertEquals($data['amount'], $transaction->amount);
        $this->assertEquals($data['type'], $transaction->type);
        $this->assertEquals($data['date'], $transaction->date->format('Y-m-d'));
        
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'description' => 'Test transaction',
            'amount' => 100.50,
        ]);
    }

    public function test_creates_expense_transaction(): void
    {
        $data = [
            'category_id' => $this->category->id,
            'description' => 'Test expense',
            'amount' => 50.25,
            'type' => 'expense',
            'date' => '2025-01-20',
        ];

        $transaction = $this->action->execute($this->user, $data);

        $this->assertEquals('expense', $transaction->type);
        $this->assertEquals(50.25, $transaction->amount);
    }

    public function test_creates_income_transaction(): void
    {
        $data = [
            'category_id' => $this->category->id,
            'description' => 'Test income',
            'amount' => 200.00,
            'type' => 'income',
            'date' => '2025-01-25',
        ];

        $transaction = $this->action->execute($this->user, $data);

        $this->assertEquals('income', $transaction->type);
        $this->assertEquals(200.00, $transaction->amount);
    }
}