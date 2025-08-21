<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticateUserActionTest extends TestCase
{
    use RefreshDatabase;

    private AuthenticateUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new AuthenticateUserAction();
    }

    public function test_can_authenticate_user_with_valid_credentials()
    {
        // Criar usuário de teste
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $result = $this->action->execute('test@example.com', 'password123');

        $this->assertTrue($result);
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_cannot_authenticate_user_with_invalid_email()
    {
        // Criar usuário de teste
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $result = $this->action->execute('wrong@example.com', 'password123');

        $this->assertFalse($result);
        $this->assertFalse(Auth::check());
    }

    public function test_cannot_authenticate_user_with_invalid_password()
    {
        // Criar usuário de teste
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $result = $this->action->execute('test@example.com', 'wrongpassword');

        $this->assertFalse($result);
        $this->assertFalse(Auth::check());
    }

    public function test_cannot_authenticate_user_with_both_invalid_credentials()
    {
        // Criar usuário de teste
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $result = $this->action->execute('wrong@example.com', 'wrongpassword');

        $this->assertFalse($result);
        $this->assertFalse(Auth::check());
    }

    public function test_can_authenticate_with_remember_option()
    {
        // Criar usuário de teste
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $result = $this->action->execute('test@example.com', 'password123', true);

        $this->assertTrue($result);
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_authentication_fails_with_nonexistent_user()
    {
        $result = $this->action->execute('nonexistent@example.com', 'anypassword');

        $this->assertFalse($result);
        $this->assertFalse(Auth::check());
    }
}