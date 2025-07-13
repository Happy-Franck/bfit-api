<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Supprimer les liaisons fixes - on ne garde que les attributs
        $this->call([
            ProductAttributeSeeder::class,
            // ProductTypeAttributeSeeder::class, // SUPPRIMÉ - plus de liaisons fixes
        ]);
    }
}
