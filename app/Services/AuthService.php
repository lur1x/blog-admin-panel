<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * @return array{user: User, access_token: string, token_type: string}
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // каст 'hashed' в User сам захеширует
            'role' => 'user',
        ]);

        return $this->tokenPayload($user);
    }

    /**
     * @return array{user: User, access_token: string, token_type: string}
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверные учётные данные.'],
            ]);
        }

        return $this->tokenPayload($user);
    }

    /**
     * @return array{user: User, access_token: string, token_type: string}
     */
    private function tokenPayload(User $user): array
    {
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
    }
}
