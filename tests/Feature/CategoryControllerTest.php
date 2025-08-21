<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function authenticated_user_can_view_categories_index()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('categories.index'));

        $response->assertStatus(200);
        $response->assertViewIs('categories.index');
    }

    /** @test */
    public function unauthenticated_user_cannot_view_categories_index()
    {
        $response = $this->get(route('categories.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_user_can_view_create_category_form()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('categories.create'));

        $response->assertStatus(200);
        $response->assertViewIs('categories.create');
    }

    /** @test */
    public function authenticated_user_can_create_category()
    {
        $this->actingAs($this->user);

        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'type' => 'expense',
            'color' => '#ff0000',
        ];

        $response = $this->post(route('categories.store'), $categoryData);

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success', 'Categoria criada com sucesso!');

        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'type' => 'expense',
            'color' => '#ff0000',
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function authenticated_user_cannot_create_category_with_invalid_data()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('categories.store'), [
            'name' => '', // Required field
            'type' => 'invalid_type', // Invalid type
        ]);

        $response->assertSessionHasErrors(['name', 'type']);
        $this->assertDatabaseCount('categories', 0);
    }

    /** @test */
    public function authenticated_user_can_view_own_category()
    {
        $this->actingAs($this->user);
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get(route('categories.show', $category));

        $response->assertStatus(200);
        $response->assertViewIs('categories.show');
        $response->assertViewHas('category', $category);
    }

    /** @test */
    public function authenticated_user_cannot_view_other_users_category()
    {
        $this->actingAs($this->user);
        $otherUser = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->get(route('categories.show', $category));

        $response->assertStatus(403);
    }

    /** @test */
    public function authenticated_user_can_view_edit_form_for_own_category()
    {
        $this->actingAs($this->user);
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get(route('categories.edit', $category));

        $response->assertStatus(200);
        $response->assertViewIs('categories.edit');
        $response->assertViewHas('category', $category);
    }

    /** @test */
    public function authenticated_user_cannot_view_edit_form_for_other_users_category()
    {
        $this->actingAs($this->user);
        $otherUser = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->get(route('categories.edit', $category));

        $response->assertStatus(403);
    }

    /** @test */
    public function authenticated_user_can_update_own_category()
    {
        $this->actingAs($this->user);
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'name' => 'Updated Category',
            'description' => 'Updated Description',
            'type' => 'income',
            'color' => '#00ff00',
        ];

        $response = $this->put(route('categories.update', $category), $updateData);

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success', 'Categoria atualizada com sucesso!');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'description' => 'Updated Description',
            'type' => 'income',
            'color' => '#00ff00',
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function authenticated_user_cannot_update_other_users_category()
    {
        $this->actingAs($this->user);
        $otherUser = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $otherUser->id]);

        $updateData = [
            'name' => 'Updated Category',
            'type' => 'income',
        ];

        $response = $this->put(route('categories.update', $category), $updateData);

        $response->assertStatus(403);
    }

    /** @test */
    public function authenticated_user_can_delete_own_category_without_transactions()
    {
        $this->actingAs($this->user);
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $response = $this->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success', "Categoria '{$category->name}' excluída com sucesso!");
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /** @test */
    public function authenticated_user_cannot_delete_category_with_transactions()
    {
        $this->actingAs($this->user);
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        
        // Create a transaction for this category
        $category->transactions()->create([
            'user_id' => $this->user->id,
            'description' => 'Test Transaction',
            'amount' => 100.00,
            'type' => 'expense',
            'date' => now(),
        ]);

        $response = $this->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('error', 'Não é possível excluir uma categoria que possui transações associadas.');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    /** @test */
    public function authenticated_user_cannot_delete_other_users_category()
    {
        $this->actingAs($this->user);
        $otherUser = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->delete(route('categories.destroy', $category));

        $response->assertStatus(403);
    }

    /** @test */
    public function categories_index_shows_only_user_categories()
    {
        $this->actingAs($this->user);
        
        // Create categories for current user
        $userCategories = Category::factory()->count(2)->create(['user_id' => $this->user->id]);
        
        // Create categories for other user
        $otherUser = User::factory()->create();
        Category::factory()->count(3)->create(['user_id' => $otherUser->id]);

        $response = $this->get(route('categories.index'));

        $response->assertStatus(200);
        $response->assertViewHas('categories');
        
        $categories = $response->viewData('categories');
        $this->assertCount(2, $categories);
        
        foreach ($categories as $category) {
            $this->assertEquals($this->user->id, $category->user_id);
        }
    }

    // Requisito 2.2: Formulário deve ter campos para nome, descrição e tipo
    /** @test */
    public function create_form_displays_required_fields()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('categories.create'));

        $response->assertStatus(200);
        $response->assertSee('name="name"', false);
        $response->assertSee('name="description"', false);
        $response->assertSee('name="type"', false);
        $response->assertSee('Receita');
        $response->assertSee('Despesa');
    }

    // Requisito 2.3: Sistema deve salvar categoria e exibir mensagem de sucesso
    /** @test */
    public function category_creation_shows_success_message()
    {
        $this->actingAs($this->user);

        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Description',
            'type' => 'expense',
        ];

        $response = $this->post(route('categories.store'), $categoryData);

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success');
        $this->assertStringContainsString('sucesso', session('success'));
    }

    // Requisito 2.4: Sistema deve permitir alteração e salvar mudanças
    /** @test */
    public function category_update_saves_changes_and_shows_success()
    {
        $this->actingAs($this->user);
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'name' => 'Updated Category Name',
            'description' => 'Updated Description',
            'type' => $category->type,
        ];

        $response = $this->put(route('categories.update', $category), $updateData);

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success');
        
        $category->refresh();
        $this->assertEquals('Updated Category Name', $category->name);
        $this->assertEquals('Updated Description', $category->description);
    }

    // Requisito 2.5: Sistema deve verificar transações associadas e solicitar confirmação
    /** @test */
    public function category_deletion_requires_confirmation_when_has_transactions()
    {
        $this->actingAs($this->user);
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        
        // Create transaction for this category
        $category->transactions()->create([
            'user_id' => $this->user->id,
            'description' => 'Test Transaction',
            'amount' => 100.00,
            'type' => 'expense',
            'date' => now(),
        ]);

        $response = $this->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString('transações associadas', session('error'));
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    // Teste adicional: Validação de tipo de categoria
    /** @test */
    public function category_type_must_be_valid()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('categories.store'), [
            'name' => 'Test Category',
            'type' => 'invalid_type',
        ]);

        $response->assertSessionHasErrors(['type']);
    }

    // Teste adicional: Nome da categoria é obrigatório
    /** @test */
    public function category_name_is_required()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('categories.store'), [
            'type' => 'expense',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    // Teste adicional: Verificar se categoria pertence ao usuário na validação
    /** @test */
    public function user_cannot_use_other_users_category_in_forms()
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($this->user);

        // Tentar criar transação com categoria de outro usuário seria testado em TransactionTest
        // Aqui testamos que não podemos editar categoria de outro usuário
        $response = $this->put(route('categories.update', $otherCategory), [
            'name' => 'Hacked Category',
            'type' => 'expense',
        ]);

        $response->assertStatus(403);
    }
}