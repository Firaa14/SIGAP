<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password123'),
                'role' => 'SO',
            ]
        );

        User::updateOrCreate(
            ['email' => 'andi@perusahaan.com'],
            [
                'name' => 'Andi',
                'password' => Hash::make('password123'),
                'role' => 'SO',
            ]
        );

        User::updateOrCreate(
            ['email' => 'rina@perusahaan.com'],
            [
                'name' => 'Rina',
                'password' => Hash::make('password123'),
                'role' => 'SO',
            ]
        );
    }
}