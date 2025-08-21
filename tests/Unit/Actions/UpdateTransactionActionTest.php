<?php

namespace Tests\Unit\Actions;

use App\Actions\UpdateTransactionAction;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTransactionActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateTransactionAction $action;
    private User $user;
    private Category $category;
    private Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->action = new UpdateTransactionAction();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['user_id' => $this->user->id]);
        $this->transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);
    }

    public function test_updates_transaction_with_valid_data(): void
    {
        $originalAmount = $this->transaction->amount;
        $originalDescription = $this->transaction->description;

        $data = [
            'category_id' => $this->category->id,
            'description' => 'Updated transaction',
            'amount' => 150.75,
            'type' => 'expense',
            'date' => '2025-01-30',
        ];

        $updatedTransaction = $this->action->execute($this->transaction, $data);

        $this->assertEquals($data['description'], $updatedTransaction->description);
        $this->assertEquals($data['amount'], $updatedTransaction->amount);
        $this->assertEquals($data['type'], $updatedTransaction->type);
        $this->assertEquals($data['date'], $updatedTransaction->date->format('Y-m-d'));
        
        $this->assertDatabaseHas('transactions', [
            'id' => $this->transaction->id,
            'description' => 'Updated transaction',
            'amount' => 150.75,
        ]);
        
        $this->assertDatabaseMissing('transactions', [
            'id' => $this->transaction->id,
            'description' => $originalDescription,
            'amount' => $originalAmount,
        ]);
    }

    public function test_updates_transaction_category(): void
    {
        $newCategory = Category::factory()->create(['user_id' => $this->user->id]);
        
        $data = [
            'category_id' => $newCategory->id,
            'description' => $this->transaction->description,
            'amount' => $this->transaction->amount,
            'type' => $this->transaction->type,
            'date' => $this->transaction->date->format('Y-m-d'),
        ];

        $updatedTransaction = $this->action->execute($this->transaction, $data);

        $this->assertEquals($newCategory->id, $updatedTransaction->category_id);
    }

    public function test_returns_fresh_transaction_instance(): void
    {
        $data = [
            'category_id' => $this->category->id,
            'description' => 'Fresh transaction',
            'amount' => $this->transaction->amount,
            'type' => $this->transaction->type,
            'date' => $this->transaction->date->format('Y-m-d'),
        ];

        $updatedTransaction = $this->action->execute($this->transaction, $data);

        $this->assertNotSame($this->transaction, $updatedTransaction);
        $this->assertEquals('Fresh transaction', $updatedTransaction->description);
    }
}