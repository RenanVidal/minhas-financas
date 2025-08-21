<?php

namespace Tests\Unit\Actions;

use App\Actions\DeleteCategoryAction;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteCategoryActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteCategoryAction $action;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new DeleteCategoryAction();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_delete_category_without_transactions()
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $result = $this->action->execute($category);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('excluída com sucesso', $result['message']);
        $this->assertEquals($category->name, $result['deleted_category']);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /** @test */
    public function it_cannot_delete_category_with_transactions()
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        
        // Create a transaction for this category
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);

        $result = $this->action->execute($category);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('possui transações associadas', $result['message']);
        $this->assertEquals('has_transactions', $result['error_type']);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    /** @test */
    public function it_can_check_if_category_can_be_deleted()
    {
        $categoryWithoutTransactions = Category::factory()->create(['user_id' => $this->user->id]);
        $categoryWithTransactions = Category::factory()->create(['user_id' => $this->user->id]);
        
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $categoryWithTransactions->id,
        ]);

        $this->assertTrue($this->action->canDelete($categoryWithoutTransactions));
        $this->assertFalse($this->action->canDelete($categoryWithTransactions));
    }

    /** @test */
    public function it_can_get_transaction_count_for_category()
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        
        // Create multiple transactions for this category
        Transaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
        ]);

        $count = $this->action->getTransactionCount($category);

        $this->assertEquals(3, $count);
    }

    /** @test */
    public function it_returns_zero_transaction_count_for_category_without_transactions()
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $count = $this->action->getTransactionCount($category);

        $this->assertEquals(0, $count);
    }

    /** @test */
    public function it_returns_success_structure_for_valid_deletion()
    {
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        $categoryName = $category->name;

        $result = $this->action->execute($category);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('deleted_category', $result);
        $this->assertEquals($categoryName, $result['deleted_category']);
    }

    /** @test */
    public function it_preserves_category_name_in_success_message()
    {
        $categoryName = 'Alimentação';
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => $categoryName,
        ]);

        $result = $this->action->execute($category);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString($categoryName, $result['message']);
        $this->assertEquals($categoryName, $result['deleted_category']);
    }
}