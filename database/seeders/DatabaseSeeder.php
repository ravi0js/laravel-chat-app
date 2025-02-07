<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; //for passsword

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password'=> Hash::make('12345'),
        ]);
        User::factory()->create([
            'name' => 'Ravi Kumar',
            'email' => 'ravi194455@example.com',
            'password'=> Hash::make('12345'),
        ]);
        User::factory()->create([
            'name' => 'Aman Bhardwaj',
            'email' => 'aman@example.com',
            'password'=> Hash::make('12345'),
        ]);
    }
}
