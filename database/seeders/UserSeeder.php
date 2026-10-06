<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'email' => 'so.upbrs@gmail.com',
                'password' => 's0sigapUPBRS',
            ],
            [
                'email' => 'syafira.so.intern@gmail.com',
                'password' => 'interns0sigap',
            ],
            [
                'email' => 'bunga.so.intern@gmail.com',
                'password' => 'interns0sigap',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'password' => Hash::make($user['password']),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
