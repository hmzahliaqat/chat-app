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
                'name'     => 'Alice Johnson',
                'email'    => 'alice@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name'     => 'Bob Smith',
                'email'    => 'bob@example.com',
                'password' => Hash::make('password'),
            ],
            [
                'name'     => 'Carol Williams',
                'email'    => 'carol@example.com',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
