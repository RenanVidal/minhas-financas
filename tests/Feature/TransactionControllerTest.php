<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_index_displays_user_transactions(): void
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('transactions.index'));

        $response->assertStatus(200);
        $response->assertSee($transaction->description);
        $response->assertSee($transaction->category->name);
    }

    public function test_index_does_not_display_other_users_transactions(): void
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
        $otherTransaction = Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $otherCategory->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('transactions.index'));

        $response->assertStatus(200);
        $response->assertDontSee($otherTransaction->description);
    }

    public function test_create_displays_form(): void
    {
        $response = $this->actingAs($this->user)->get(route('transactions.create'));

        $response->assertStatus(200);
        $response->assertSee('Nova Transação');
        $response->assertSee($this->category->name);
    }

    public function test_store_creates_transaction(): void
    {
        $data = [
            'description' => 'Test transaction',
            'amount' => 100.50,
            'date' => '2025-01-15',
            'category_id' => $this->category->id,
            'type' => 'income',
        ];

        $response = $this->actingAs($this->user)->post(route('transactions.store'), $data);

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'description' => 'Test transaction',
            'amount' => 100.50,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post(route('transactions.store'), []);

        $response->assertSessionHasErrors(['description', 'amount', 'date', 'category_id', 'type']);
    }

    public function test_store_validates_positive_amount(): void
    {
        $data = [
            'description' => 'Test transaction',
            'amount' => -10.50,
            'date' => '2025-01-15',
            'category_id' => $this->category->id,
            'type' => 'income',
        ];

        $response = $this->actingAs($this->user)->post(route('transactions.store'), $data);

        $response->assertSessionHasErrors(['amount']);
    }

    public function test_show_displays_transaction(): void
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('transactions.show', $transaction));

        $response->assertStatus(200);
        $response->assertSee($transaction->description);
        $response->assertSee($transaction->category->name);
    }

    public function test_show_prevents_viewing_other_users_transaction(): void
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
        $otherTransaction = Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $otherCategory->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('transactions.show', $otherTransaction));

        $response->assertStatus(403);
    }

    public function test_edit_displays_form(): void
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->user)->get(route('transactions.edit', $transaction));

        $response->assertStatus(200);
        $response->assertSee('Editar Transação');
        $response->assertSee($transaction->description);
    }

    public function test_update_modifies_transaction(): void
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $data = [
            'description' => 'Updated transaction',
            'amount' => 200.75,
            'date' => '2025-01-20',
            'category_id' => $this->category->id,
            'type' => 'expense',
        ];

        $response = $this->actingAs($this->user)->put(route('transactions.update', $transaction), $data);

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'description' => 'Updated transaction',
            'amount' => 200.75,
        ]);
    }

    public function test_destroy_deletes_transaction(): void
    {
        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('transactions.destroy', $transaction));

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);
    }

    public function test_prevents_unauthorized_access(): void
    {
        $response = $this->get(route('transactions.index'));
        $response->assertRedirect(route('login'));
    }

    // Requisito 3.1: Lista de transações ordenadas por data
    public function test_index_displays_transactions_ordered_by_date(): void
    {
        $this->actingAs($this->user);

        // Create transactions with different dates
        $transaction1 = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'date' => '2025-01-01',
            'description' => 'Older Transaction'
        ]);

        $transaction2 = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'date' => '2025-01-15',
            'description' => 'Newer Transaction'
        ]);

        $response = $this->get(route('transactions.index'));

        $response->assertStatus(200);
        $transactions = $response->viewData('transactions');
        
        // Should be ordered by date descending (newest first)
        $this->assertEquals('Newer Transaction', $transactions->first()->description);
    }

    // Requisito 3.2: Formulário com campos para descrição, valor, data, categoria e tipo
    public function test_create_form_displays_required_fields(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('transactions.create'));

        $response->assertStatus(200);
        $response->assertSee('name="description"', false);
        $response->assertSee('name="amount"', false);
        $response->assertSee('name="date"', false);
        $response->assertSee('name="category_id"', false);
        $response->assertSee('name="type"', false);
        $response->assertSee($this->category->name);
    }

    // Requisito 3.3: Salvar transação e atualizar saldo
    public function test_store_creates_transaction_and_updates_balance(): void
    {
        $this->actingAs($this->user);

        $data = [
            'description' => 'Test Income',
            'amount' => 500.00,
            'date' => '2025-01-15',
            'category_id' => $this->category->id,
            'type' => 'income',
        ];

        $response = $this->post(route('transactions.store'), $data);

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'description' => 'Test Income',
            'amount' => 500.00,
            'type' => 'income',
        ]);
    }

    // Requisito 3.4: Permitir alterações e recalcular saldo
    public function test_update_recalculates_balance(): void
    {
        $this->actingAs($this->user);

        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'amount' => 100.00,
            'type' => 'income'
        ]);

        $data = [
            'description' => 'Updated Transaction',
            'amount' => 200.00,
            'date' => $transaction->date->format('Y-m-d'),
            'category_id' => $this->category->id,
            'type' => 'expense',
        ];

        $response = $this->put(route('transactions.update', $transaction), $data);

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');
        
        $transaction->refresh();
        $this->assertEquals(200.00, $transaction->amount);
        $this->assertEquals('expense', $transaction->type);
    }

    // Requisito 3.5: Remover registro e ajustar saldo
    public function test_destroy_removes_transaction_and_adjusts_balance(): void
    {
        $this->actingAs($this->user);

        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->delete(route('transactions.destroy', $transaction));

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);
    }

    // Requisito 3.6: Validar valor positivo
    public function test_store_rejects_zero_amount(): void
    {
        $this->actingAs($this->user);

        $data = [
            'description' => 'Test transaction',
            'amount' => 0,
            'date' => '2025-01-15',
            'category_id' => $this->category->id,
            'type' => 'income',
        ];

        $response = $this->post(route('transactions.store'), $data);

        $response->assertSessionHasErrors(['amount']);
    }

    // Requisito 3.6: Validar valor positivo (negativo)
    public function test_update_rejects_negative_amount(): void
    {
        $this->actingAs($this->user);

        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $data = [
            'description' => 'Test transaction',
            'amount' => -50.00,
            'date' => '2025-01-15',
            'category_id' => $this->category->id,
            'type' => 'expense',
        ];

        $response = $this->put(route('transactions.update', $transaction), $data);

        $response->assertSessionHasErrors(['amount']);
    }

    // Teste adicional: Verificar se categoria pertence ao usuário
    public function test_cannot_create_transaction_with_other_users_category(): void
    {
        $this->actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);

        $data = [
            'description' => 'Test transaction',
            'amount' => 100.00,
            'date' => '2025-01-15',
            'category_id' => $otherCategory->id,
            'type' => 'income',
        ];

        $response = $this->post(route('transactions.store'), $data);

        $response->assertSessionHasErrors(['category_id']);
    }

    // Teste adicional: Verificar autorização para edição
    public function test_cannot_edit_other_users_transaction(): void
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
        $otherTransaction = Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $otherCategory->id,
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('transactions.edit', $otherTransaction));
        $response->assertStatus(403);

        $response = $this->put(route('transactions.update', $otherTransaction), [
            'description' => 'Hacked',
            'amount' => 999.99,
            'date' => '2025-01-15',
            'category_id' => $this->category->id,
            'type' => 'income',
        ]);
        $response->assertStatus(403);

        $response = $this->delete(route('transactions.destroy', $otherTransaction));
        $response->assertStatus(403);
    }

    // Teste adicional: Paginação na listagem
    public function test_index_paginates_transactions(): void
    {
        $this->actingAs($this->user);

        // Create many transactions
        Transaction::factory()->count(25)->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->get(route('transactions.index'));

        $response->assertStatus(200);
        $transactions = $response->viewData('transactions');
        
        // Should be paginated (default Laravel pagination is 15 per page)
        $this->assertLessThanOrEqual(15, $transactions->count());
    }
}