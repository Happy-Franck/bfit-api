<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProduitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('produits')->insert([
            'name' => 'Pure whey isolate',
            'image' => 'test.png',
            'description' => 'description lava be',
            'poid' => 1,
            'price' => 50000,
            'user_id' => 1,
        ]);
        DB::table('produits')->insert([
            'name' => 'Favre iso 100% whey zero',
            'image' => 'test.png',
            'description' => 'description lava be',
            'poid' => 1,
            'price' => 25000,
            'user_id' => 2,
        ]);
        DB::table('produits')->insert([
            'name' => 'Big mass gainer',
            'image' => 'test.png',
            'description' => 'description lava be',
            'poid' => 1,
            'price' => 65000,
            'user_id' => 1,
        ]);
        DB::table('produits')->insert([
            'name' => 'Creative pro zero',
            'image' => 'test.png',
            'description' => 'description lava be',
            'poid' => 1,
            'price' => 40000,
            'user_id' => 2,
        ]);
    }
}
