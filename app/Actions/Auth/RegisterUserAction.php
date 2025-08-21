<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegisterUserAction
{
    /**
     * Registra novo usuário com validação de email único
     * 
     * @param array $data
     * @return User
     * @throws ValidationException
     */
    public function execute(array $data): User
    {
        // Validar se email já existe
        if (User::where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Este email já está sendo usado por outro usuário.']
            ]);
        }

        // Criar novo usuário
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}