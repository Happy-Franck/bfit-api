<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1- Joker = Admin
        User::create([
            'name' => 'Joker Black',
            'email' => 'nikola@gmail.com',
            'password' => bcrypt('nikolanikola'),
            'avatar' => 'the_joker.png',
        ])->assignRole('administrateur');

        // 2- Joker = Admin
        User::create([
            'name' => 'Joker Red',
            'email' => 'jokic@gmail.com',
            'password' => bcrypt('jokicjokic'),
            'avatar' => 'the_joker.png',
        ])->assignRole('administrateur');

        // 3- Lebron = Coach
        User::create([
            'name' => 'The King',
            'email' => 'lebron@gmail.com',
            'password' => bcrypt('lebronlebron'),
            'avatar' => 'the_king.png',
        ])->assignRole('coach');

        // 4- Zion = Coach
        User::create([
            'name' => 'Zanos',
            'email' => 'zion@gmail.com',
            'password' => bcrypt('zionzion'),
            'avatar' => 'zanos.png',
        ])->assignRole('coach');

        // 5- KD = Challenger
        User::create([
            'name' => 'KD',
            'email' => 'durant@gmail.com',
            'password' => bcrypt('durantdurant'),
            'avatar' => 'kd.png',
        ])->assignRole('challenger');

        // 6- Lillard = Challenger
        User::create([
            'name' => 'Dame Time',
            'email' => 'lillard@gmail.com',
            'password' => bcrypt('lillardlillard'),
            'avatar' => 'lillard.png',
        ])->assignRole('challenger');
    }
}
