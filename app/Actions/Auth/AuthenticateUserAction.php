<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Auth;

class AuthenticateUserAction
{
    /**
     * Autentica usuário com credenciais
     * 
     * @param string $email
     * @param string $password
     * @param bool $remember
     * @return bool
     */
    public function execute(string $email, string $password, bool $remember = false): bool
    {
        $credentials = [
            'email' => $email,
            'password' => $password,
        ];

        return Auth::attempt($credentials, $remember);
    }
}