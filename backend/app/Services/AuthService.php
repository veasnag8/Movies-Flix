<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(protected GoogleSheetService $sheets)
    {
    }

    /**
     * Sync a Google Sheets user into the local SQLite users table
     * so Laravel Sanctum can issue API tokens.
     * Google Sheets remains the source of truth for user records.
     */
    public function syncLocalUser(array $sheetUser): User
    {
        $user = User::find($sheetUser['id']);

        $payload = [
            'name' => $sheetUser['name'] ?? '',
            'email' => $sheetUser['email'] ?? '',
            'role' => $sheetUser['role'] ?? 'user',
            'avatar' => $sheetUser['avatar'] ?? '',
        ];

        if ($user) {
            $user->fill($payload);
            if (! empty($sheetUser['password']) && ! str_starts_with($sheetUser['password'], '$2y$')) {
                $user->password = Hash::make($sheetUser['password']);
            } elseif (! empty($sheetUser['password'])) {
                $user->password = $sheetUser['password'];
            }
            $user->save();

            return $user->fresh();
        }

        $user = new User($payload);
        $user->id = (int) $sheetUser['id'];
        $user->password = $sheetUser['password'] ?? Hash::make(str()->random(32));
        $user->save();

        return $user;
    }

    public function register(array $data): array
    {
        if ($this->sheets->getUserByEmail($data['email'])) {
            throw new \InvalidArgumentException('Email is already registered.');
        }

        $hashed = Hash::make($data['password']);

        $sheetUser = $this->sheets->createUser([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $hashed,
            'role' => $data['role'] ?? 'user',
            'avatar' => $data['avatar'] ?? '',
        ]);

        $user = $this->syncLocalUser($sheetUser);
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->toPublicArray(),
            'token' => $token,
        ];
    }

    public function login(string $email, string $password): array
    {
        $sheetUser = $this->sheets->getUserByEmail($email);

        if (! $sheetUser || ! Hash::check($password, $sheetUser['password'] ?? '')) {
            throw new \InvalidArgumentException('Invalid email or password.');
        }

        $user = $this->syncLocalUser($sheetUser);
        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->toPublicArray(),
            'token' => $token,
        ];
    }
}
