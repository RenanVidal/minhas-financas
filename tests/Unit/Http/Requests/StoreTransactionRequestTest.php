<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTransactionRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['user_id' => $this->user->id]);
        
        $this->actingAs($this->user);
    }

    public function test_validates_required_fields(): void
    {
        $request = new StoreTransactionRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('description', $validator->errors()->toArray());
        $this->assertArrayHasKey('amount', $validator->errors()->toArray());
        $this->assertArrayHasKey('date', $validator->errors()->toArray());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
    }

    public function test_validates_amount_is_positive(): void
    {
        $request = new StoreTransactionRequest();
        
        $validator = Validator::make([
            'amount' => 0
        ], $request->rules());
        
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('amount', $validator->errors()->toArray());
        
        $validator = Validator::make([
            'amount' => -10.50
        ], $request->rules());
        
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('amount', $validator->errors()->toArray());
    }

    public function test_validates_category_belongs_to_user(): void
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
        
        $request = new StoreTransactionRequest();
        
        $validator = Validator::make([
            'category_id' => $otherCategory->id
        ], $request->rules());
        
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
    }

    public function test_validates_type_is_valid(): void
    {
        $request = new StoreTransactionRequest();
        
        $validator = Validator::make([
            'type' => 'invalid_type'
        ], $request->rules());
        
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
    }

    public function test_passes_with_valid_data(): void
    {
        $request = new StoreTransactionRequest();
        
        $validator = Validator::make([
            'description' => 'Test transaction',
            'amount' => 100.50,
            'date' => '2025-01-15',
            'category_id' => $this->category->id,
            'type' => 'income'
        ], $request->rules());
        
        $this->assertTrue($validator->passes());
    }

    public function test_authorize_returns_true(): void
    {
        $request = new StoreTransactionRequest();
        
        $this->assertTrue($request->authorize());
    }
}