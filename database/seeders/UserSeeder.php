<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun demo (password semua: "password"). Dicantumkan juga di README.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Admin Afindo', 'email' => 'admin@afindo.test', 'role' => UserRole::Admin],
            ['name' => 'Budi Santoso', 'email' => 'student@afindo.test', 'role' => UserRole::Student],
            ['name' => 'Siti Rahmawati', 'email' => 'siti@afindo.test', 'role' => UserRole::Student],
        ];

        foreach ($accounts as $data) {
            $user = User::firstOrNew(['email' => $data['email']]);
            $user->forceFill([
                'name' => $data['name'],
                'password' => 'password',
                'role' => $data['role'],
                'email_verified_at' => now(),
            ])->save();
        }
    }
}
