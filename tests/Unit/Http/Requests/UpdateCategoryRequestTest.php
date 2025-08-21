<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateCategoryRequestTest extends TestCase
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

    /** @test */
    public function it_passes_validation_with_valid_data()
    {
        $request = new UpdateCategoryRequest();
        $category = $this->category;
        $request->setRouteResolver(function () use ($category) {
            return new class($category) {
                private $category;
                
                public function __construct($category) {
                    $this->category = $category;
                }
                
                public function parameter($key) {
                    return $this->category;
                }
            };
        });

        $validator = Validator::make([
            'name' => 'Updated Category',
            'description' => 'Updated description',
            'type' => 'income',
            'color' => '#00ff00',
        ], $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_allows_same_name_when_updating_same_category()
    {
        $request = new UpdateCategoryRequest();
        $category = $this->category;
        $request->setRouteResolver(function () use ($category) {
            return new class($category) {
                private $category;
                
                public function __construct($category) {
                    $this->category = $category;
                }
                
                public function parameter($key) {
                    return $this->category;
                }
            };
        });

        $validator = Validator::make([
            'name' => $this->category->name,
            'type' => $this->category->type,
        ], $request->rules(), $request->messages());

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function it_validates_unique_name_when_updating_to_existing_name()
    {
        $existingCategory = Category::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Existing Category',
        ]);

        $request = new UpdateCategoryRequest();
        $category = $this->category;
        $request->setRouteResolver(function () use ($category) {
            return new class($category) {
                private $category;
                
                public function __construct($category) {
                    $this->category = $category;
                }
                
                public function parameter($key) {
                    return $this->category;
                }
            };
        });

        $validator = Validator::make([
            'name' => 'Existing Category',
            'type' => 'expense',
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertEquals('Você já possui uma categoria com este nome.', $validator->errors()->first('name'));
    }

    /** @test */
    public function it_requires_name()
    {
        $request = new UpdateCategoryRequest();
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
        $request = new UpdateCategoryRequest();
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
        $request = new UpdateCategoryRequest();
        $validator = Validator::make([
            'name' => 'Test Category',
            'type' => 'invalid_type',
        ], $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('type', $validator->errors()->toArray());
        $this->assertEquals('O tipo deve ser receita ou despesa.', $validator->errors()->first('type'));
    }

    /** @test */
    public function authorize_returns_true_when_user_owns_category()
    {
        $request = new UpdateCategoryRequest();
        $category = $this->category;
        $request->setRouteResolver(function () use ($category) {
            return new class($category) {
                private $category;
                
                public function __construct($category) {
                    $this->category = $category;
                }
                
                public function parameter($key) {
                    return $this->category;
                }
            };
        });

        $this->assertTrue($request->authorize());
    }

    /** @test */
    public function authorize_returns_false_when_user_does_not_own_category()
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);

        $request = new UpdateCategoryRequest();
        $request->setRouteResolver(function () use ($otherCategory) {
            return new class($otherCategory) {
                private $category;
                
                public function __construct($category) {
                    $this->category = $category;
                }
                
                public function parameter($key) {
                    return $this->category;
                }
            };
        });

        $this->assertFalse($request->authorize());
    }

    /** @test */
    public function authorize_returns_false_when_user_is_not_authenticated()
    {
        auth()->logout();
        
        $request = new UpdateCategoryRequest();
        $category = $this->category;
        $request->setRouteResolver(function () use ($category) {
            return new class($category) {
                private $category;
                
                public function __construct($category) {
                    $this->category = $category;
                }
                
                public function parameter($key) {
                    return $this->category;
                }
            };
        });
        
        $this->assertFalse($request->authorize());
    }
}