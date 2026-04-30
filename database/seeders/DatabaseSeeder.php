<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Skillify Admin',
                'password' => Hash::make('Admin12345!'),
                'role' => 'admin',
                'account_status' => 'active',
                'requested_role' => null,
                'dosen_request_status' => 'none',
            ]
        );

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => 'student',
                'account_status' => 'active',
                'requested_role' => 'student',
                'dosen_request_status' => 'none',
            ]
        );
    }
}
