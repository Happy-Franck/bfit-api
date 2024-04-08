<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create(['name' => 'Pectoraux']);
        Category::create(['name' => 'Abdominaux']);
        Category::create(['name' => 'Obliques']);
        Category::create(['name' => 'Grands dorsaux']);
        Category::create(['name' => 'Rhomboïdes']);
        Category::create(['name' => 'Trapèzes']);
        Category::create(['name' => 'Déltoïdes']);
        Category::create(['name' => 'Biceps']);
        Category::create(['name' => 'Triceps']);
        Category::create(['name' => 'Brachiaux']);
        Category::create(['name' => 'Avant-bras']);
        Category::create(['name' => 'Fessier']);
        Category::create(['name' => 'Quadriceps']);
        Category::create(['name' => 'Ishio']);
        Category::create(['name' => 'Mollets']);
    }
}
