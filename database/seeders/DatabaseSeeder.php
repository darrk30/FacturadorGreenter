<?php

namespace Database\Seeders;

use App\Models\User;
// Quita o comenta la línea de WithoutModelEvents
// use Illuminate\Database\Console\Seeds\WithoutModelEvents; 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    // Quita o comenta el uso del trait aquí adentro también
    // use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Kevin Rivera Rojas',
            'email' => 'kriverarojas44@gmail.com',
            'password' => Hash::make('AdminKevin2026!*'),
        ]);
    }
}