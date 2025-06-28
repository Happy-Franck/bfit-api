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
            'telephone' => '0123456789',
            'cin' => 'AB123456',
            'taille' => 1.80,
            'poids' => [['date' => '2024-01-01', 'valeur' => 75.5]],
            'objectif' => 'maintien',
            'sexe' => 'homme',
            'date_naissance' => '1990-01-15',
        ])->assignRole('administrateur');

        // 2- Joker = Admin
        User::create([
            'name' => 'Joker Red',
            'email' => 'jokic@gmail.com',
            'password' => bcrypt('jokicjokic'),
            'avatar' => 'the_joker.png',
            'telephone' => '0123456790',
            'cin' => 'CD789012',
            'taille' => 1.75,
            'poids' => [['date' => '2024-01-01', 'valeur' => 70.0]],
            'objectif' => 'maintien',
            'sexe' => 'homme',
            'date_naissance' => '1992-03-20',
        ])->assignRole('administrateur');

        // 3- Lebron = Coach
        User::create([
            'name' => 'The King',
            'email' => 'lebron@gmail.com',
            'password' => bcrypt('lebronlebron'),
            'avatar' => 'the_king.png',
            'telephone' => '0123456791',
            'cin' => 'EF345678',
            'taille' => 2.06,
            'poids' => [['date' => '2024-01-01', 'valeur' => 113.0]],
            'objectif' => 'prise de force',
            'sexe' => 'homme',
            'date_naissance' => '1984-12-30',
        ])->assignRole('coach');

        // 4- Zion = Coach
        User::create([
            'name' => 'Zanos',
            'email' => 'zion@gmail.com',
            'password' => bcrypt('zionzion'),
            'avatar' => 'zanos.png',
            'telephone' => '0123456792',
            'cin' => 'GH901234',
            'taille' => 1.98,
            'poids' => [['date' => '2024-01-01', 'valeur' => 129.0]],
            'objectif' => 'prise de masse',
            'sexe' => 'homme',
            'date_naissance' => '2000-07-06',
        ])->assignRole('coach');

        // 5- KD = Challenger
        User::create([
            'name' => 'KD',
            'email' => 'durant@gmail.com',
            'password' => bcrypt('durantdurant'),
            'avatar' => 'kd.png',
            'telephone' => '0123456793',
            'cin' => 'IJ567890',
            'taille' => 2.11,
            'poids' => [['date' => '2024-01-01', 'valeur' => 109.0]],
            'objectif' => 'performance',
            'sexe' => 'homme',
            'date_naissance' => '1988-09-29',
        ])->assignRole('challenger');

        // 6- Lillard = Challenger
        User::create([
            'name' => 'Dame Time',
            'email' => 'lillard@gmail.com',
            'password' => bcrypt('lillardlillard'),
            'avatar' => 'lillard.png',
            'telephone' => '0123456794',
            'cin' => 'KL123456',
            'taille' => 1.88,
            'poids' => [['date' => '2024-01-01', 'valeur' => 88.0]],
            'objectif' => 'endurance',
            'sexe' => 'homme',
            'date_naissance' => '1990-07-15',
        ])->assignRole('challenger');
    }
}
