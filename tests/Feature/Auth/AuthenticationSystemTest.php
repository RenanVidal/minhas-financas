<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationSystemTest extends TestCase
{
    use RefreshDatabase;

    // Requisito 1.1: Exibir formulário de registro com campos para nome, email e senha
    public function test_registration_screen_displays_required_fields()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('name="name"', false);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee('name="password_confirmation"', false);
    }

    // Requisito 1.2: Criar nova conta e redirecionar para dashboard com dados válidos
    public function test_new_users_can_register_with_valid_data()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
        
        // Verificar se usuário foi criado no banco
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    // Requisito 1.3: Exibir mensagem de erro para email já existente
    public function test_registration_fails_with_duplicate_email()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    // Teste adicional: Validação de campos obrigatórios no registro
    public function test_registration_requires_all_fields()
    {
        $response = $this->post('/register', []);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
    }

    // Teste adicional: Validação de confirmação de senha
    public function test_registration_requires_password_confirmation()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest();
    }

    // Requisito 1.4: Exibir campos para email e senha na página de login
    public function test_login_screen_displays_required_fields()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
    }

    // Requisito 1.5: Autenticar usuário e redirecionar para dashboard com credenciais válidas
    public function test_users_can_authenticate_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
    }

    // Requisito 1.6: Exibir mensagem de erro com credenciais inválidas
    public function test_users_cannot_authenticate_with_invalid_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    // Requisito 1.6: Exibir mensagem de erro com email inexistente
    public function test_users_cannot_authenticate_with_invalid_email()
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    // Teste adicional: Validação de campos obrigatórios no login
    public function test_login_requires_email_and_password()
    {
        $response = $this->post('/login', []);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    // Teste de proteção de rotas: Dashboard requer autenticação
    public function test_dashboard_requires_authentication()
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    // Teste de acesso: Usuários autenticados podem acessar dashboard
    public function test_authenticated_users_can_access_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }

    // Teste de proteção: Todas as rotas protegidas requerem autenticação
    public function test_protected_routes_require_authentication()
    {
        $protectedRoutes = [
            '/categories',
            '/categories/create',
            '/transactions',
            '/transactions/create',
            '/reports',
            '/goals',
            '/goals/create',
            '/profile',
            '/achievements'
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login', "Route {$route} should redirect to login");
        }
    }

    // Teste de logout: Usuários podem fazer logout
    public function test_users_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    // Teste de redirecionamento: Usuários autenticados são redirecionados do login
    public function test_authenticated_users_are_redirected_from_login()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/dashboard');
    }

    // Teste de redirecionamento: Usuários autenticados são redirecionados do registro
    public function test_authenticated_users_are_redirected_from_register()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/register');

        $response->assertRedirect('/dashboard');
    }

    // Teste de fluxo completo: Registro seguido de logout e login
    public function test_complete_authentication_flow()
    {
        // 1. Registrar novo usuário
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));

        // 2. Fazer logout
        $response = $this->post('/logout');
        $this->assertGuest();

        // 3. Fazer login novamente
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
    }

    // Teste de sessão: Verificar se sessão persiste entre requests
    public function test_authentication_session_persists()
    {
        $user = User::factory()->create();

        // Login
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();

        // Acessar rota protegida
        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        // Verificar se ainda está autenticado
        $this->assertAuthenticated();
    }
}