<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(RolePermissionSeeder::class);
        $this->call(UserSeeder::class);
        
        // Seeders e-commerce - ProductTypeSeeder doit être appelé avant ProduitSeeder
        $this->call(ProductTypeSeeder::class);
        
        // NOUVEAU : Système flexible sans contraintes fixes
        $this->call(FlexibleProductSystemSeeder::class);
        
        $this->call(ProduitSeeder::class);
        
        $this->call(CategorySeeder::class);
        $this->call(EquipmentSeeder::class);
        $this->call(TrainingSeeder::class);
    }
}
