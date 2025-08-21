<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\UpdateGoalRequest;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateGoalRequestTest extends TestCase
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

    public function test_validates_required_fields(): void
    {
        $request = new UpdateGoalRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('target_amount', $validator->errors()->toArray());
        $this->assertArrayHasKey('deadline', $validator->errors()->toArray());
    }

    public function test_validates_name_max_length(): void
    {
        $request = new UpdateGoalRequest();
        $data = [
            'name' => str_repeat('a', 256),
            'target_amount' => 1000,
            'deadline' => now()->addDays(30)->format('Y-m-d'),
        ];
        
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_validates_target_amount_positive(): void
    {
        $request = new UpdateGoalRequest();
        $data = [
            'name' => 'Meta Teste',
            'target_amount' => 0,
            'deadline' => now()->addDays(30)->format('Y-m-d'),
        ];
        
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('target_amount', $validator->errors()->toArray());
    }

    public function test_validates_deadline_future(): void
    {
        $request = new UpdateGoalRequest();
        $data = [
            'name' => 'Meta Teste',
            'target_amount' => 1000,
            'deadline' => now()->subDays(1)->format('Y-m-d'),
        ];
        
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('deadline', $validator->errors()->toArray());
    }

    public function test_passes_with_valid_data(): void
    {
        $request = new UpdateGoalRequest();
        $data = [
            'name' => 'Meta Teste',
            'target_amount' => 1000,
            'deadline' => now()->addDays(30)->format('Y-m-d'),
            'category_id' => $this->category->id,
        ];
        
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validates_category_belongs_to_user(): void
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
        
        $this->actingAs($this->user);
        
        $request = new UpdateGoalRequest();
        $request->merge([
            'name' => 'Meta Teste',
            'target_amount' => 1000,
            'deadline' => now()->addDays(30)->format('Y-m-d'),
            'category_id' => $otherCategory->id,
        ]);
        
        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);
        
        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
    }
}