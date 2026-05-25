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
                'name' => 'Jhon Doe',
                'username' => 'jhon_doe',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Jane Doe',
                'username' => 'jane_doe',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $user) {
            User::firstOrCreate(
                ['username' => $user['username']],
                $user
            );
        }

        $this->command->info('Users seeded: ' . count($users) . ' records.');
    }
}
