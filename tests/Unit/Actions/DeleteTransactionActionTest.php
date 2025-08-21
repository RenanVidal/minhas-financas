<?php

namespace Tests\Unit\Actions;

use App\Actions\DeleteTransactionAction;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteTransactionActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteTransactionAction $action;
    private User $user;
    private Category $category;
    private Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->action = new DeleteTransactionAction();
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['user_id' => $this->user->id]);
        $this->transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);
    }

    public function test_deletes_transaction_successfully(): void
    {
        $transactionId = $this->transaction->id;
        
        $result = $this->action->execute($this->transaction);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('transactions', [
            'id' => $transactionId,
        ]);
    }

    public function test_deletes_income_transaction(): void
    {
        $incomeTransaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'income',
            'amount' => 100.00,
        ]);
        
        $transactionId = $incomeTransaction->id;
        
        $result = $this->action->execute($incomeTransaction);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('transactions', [
            'id' => $transactionId,
        ]);
    }

    public function test_deletes_expense_transaction(): void
    {
        $expenseTransaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'type' => 'expense',
            'amount' => 50.00,
        ]);
        
        $transactionId = $expenseTransaction->id;
        
        $result = $this->action->execute($expenseTransaction);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('transactions', [
            'id' => $transactionId,
        ]);
    }
}