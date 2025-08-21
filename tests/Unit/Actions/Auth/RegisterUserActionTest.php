<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RegisterUserActionTest extends TestCase
{
    use RefreshDatabase;

    private RegisterUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new RegisterUserAction();
    }

    public function test_can_register_new_user_with_valid_data()
    {
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123'
        ];

        $user = $this->action->execute($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('João Silva', $user->name);
        $this->assertEquals('joao@example.com', $user->email);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertDatabaseHas('users', [
            'name' => 'João Silva',
            'email' => 'joao@example.com'
        ]);
    }

    public function test_throws_validation_exception_when_email_already_exists()
    {
        // Criar usuário existente
        User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'Novo Usuário',
            'email' => 'existing@example.com',
            'password' => 'password123'
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Este email já está sendo usado por outro usuário.');

        $this->action->execute($userData);
    }

    public function test_password_is_properly_hashed()
    {
        $userData = [
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'password' => 'mypassword'
        ];

        $user = $this->action->execute($userData);

        $this->assertNotEquals('mypassword', $user->password);
        $this->assertTrue(Hash::check('mypassword', $user->password));
    }

    public function test_user_count_increases_after_registration()
    {
        $initialCount = User::count();

        $userData = [
            'name' => 'Pedro Costa',
            'email' => 'pedro@example.com',
            'password' => 'password123'
        ];

        $this->action->execute($userData);

        $this->assertEquals($initialCount + 1, User::count());
    }
}