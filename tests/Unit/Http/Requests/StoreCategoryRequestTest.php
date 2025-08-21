<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreCategoryRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_passes_validation_with_valid_data()
    {
        $request = new StoreCategoryRequest();
        $validator = Validator::make([
            'name' => 'Alimentação',
            'description' => 'Gastos com comida e bebida',
            'type' => 'expense',
            'color' => '#ff0000',
        ], $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_requires_name()
    {
        $request = new StoreCategoryRequest();
        $validator = Validator::make([
            'type' => 'expense',
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertEquals('O nome da categoria é obrigatório.', $validator->errors()->first('name'));
    }

    /** @test */
    public function it_requires_type()
    {
        $request = new StoreCategoryRequest();
        $validator = Validator::make([
            'name' => 'Test Category',
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
        $this->assertEquals('O tipo da categoria é obrigatório.', $validator->errors()->first('type'));
    }

    /** @test */
    public function it_validates_type_values()
    {
        $request = new StoreCategoryRequest();
        $validator = Validator::make([
            'name' => 'Test Category',
            'type' => 'invalid_type',
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
        $this->assertEquals('O tipo deve ser receita ou despesa.', $validator->errors()->first('type'));
    }

    /** @test */
    public function it_validates_name_length()
    {
        $request = new StoreCategoryRequest();
        $validator = Validator::make([
            'name' => str_repeat('a', 256), // 256 characters
            'type' => 'expense',
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertEquals('O nome da categoria não pode ter mais de 255 caracteres.', $validator->errors()->first('name'));
    }

    /** @test */
    public function it_validates_description_length()
    {
        $request = new StoreCategoryRequest();
        $validator = Validator::make([
            'name' => 'Test Category',
            'description' => str_repeat('a', 1001), // 1001 characters
            'type' => 'expense',
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('description', $validator->errors()->toArray());
        $this->assertEquals('A descrição não pode ter mais de 1000 caracteres.', $validator->errors()->first('description'));
    }

    /** @test */
    public function it_validates_color_format()
    {
        $request = new StoreCategoryRequest();
        $validator = Validator::make([
            'name' => 'Test Category',
            'type' => 'expense',
            'color' => 'invalid-color',
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('color', $validator->errors()->toArray());
        $this->assertEquals('A cor deve estar no formato hexadecimal (#RRGGBB).', $validator->errors()->first('color'));
    }

    /** @test */
    public function it_accepts_valid_color_formats()
    {
        $request = new StoreCategoryRequest();
        
        $validColors = ['#ff0000', '#00FF00', '#0000ff', '#123ABC'];
        
        foreach ($validColors as $color) {
            $validator = Validator::make([
                'name' => 'Test Category',
                'type' => 'expense',
                'color' => $color,
            ], $request->rules(), $request->messages());

            $this->assertFalse($validator->fails(), "Color {$color} should be valid");
        }
    }

    /** @test */
    public function it_validates_unique_name_per_user()
    {
        // Create a category for the current user
        Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Existing Category',
        ]);

        $request = new StoreCategoryRequest();
        $validator = Validator::make([
            'name' => 'Existing Category',
            'type' => 'expense',
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertEquals('Você já possui uma categoria com este nome.', $validator->errors()->first('name'));
    }

    /** @test */
    public function it_allows_same_name_for_different_users()
    {
        $otherUser = User::factory()->create();
        
        // Create a category for another user
        Category::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'Shared Name',
        ]);

        $request = new StoreCategoryRequest();
        $validator = Validator::make([
            'name' => 'Shared Name',
            'type' => 'expense',
        ], $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_allows_nullable_description()
    {
        $request = new StoreCategoryRequest();
        $validator = Validator::make([
            'name' => 'Test Category',
            'type' => 'expense',
            'description' => null,
        ], $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_allows_nullable_color()
    {
        $request = new StoreCategoryRequest();
        $validator = Validator::make([
            'name' => 'Test Category',
            'type' => 'expense',
            'color' => null,
        ], $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function authorize_returns_true_when_user_is_authenticated()
    {
        $request = new StoreCategoryRequest();
        
        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function authorize_returns_false_when_user_is_not_authenticated()
    {
        auth()->logout();
        
        $request = new StoreCategoryRequest();
        
        $this->assertFalse($request->authorize());
    }
}