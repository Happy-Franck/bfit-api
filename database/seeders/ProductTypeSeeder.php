<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductType;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productTypes = [
            [
                'name' => 'Protéines',
                'slug' => 'proteines',
                'description' => 'Suppléments protéinés pour la prise de masse musculaire',
                'is_active' => true
            ],
            [
                'name' => 'Créatine',
                'slug' => 'creatine',
                'description' => 'Suppléments de créatine pour améliorer les performances',
                'is_active' => true
            ],
            [
                'name' => 'Gainers',
                'slug' => 'gainers',
                'description' => 'Suppléments pour la prise de poids et de masse',
                'is_active' => true
            ],
            [
                'name' => 'Brûleurs de graisse',
                'slug' => 'bruleurs-de-graisse',
                'description' => 'Suppléments pour la perte de poids et la définition musculaire',
                'is_active' => true
            ],
            [
                'name' => 'Vitamines & Minéraux',
                'slug' => 'vitamines-mineraux',
                'description' => 'Compléments vitaminiques et minéraux',
                'is_active' => true
            ],
            [
                'name' => 'Barres énergétiques',
                'slug' => 'barres-energetiques',
                'description' => 'Barres nutritionnelles pour l\'énergie et la récupération',
                'is_active' => true
            ],
            [
                'name' => 'Vêtements',
                'slug' => 'vetements',
                'description' => 'Vêtements de sport et accessoires de fitness',
                'is_active' => true
            ],
            [
                'name' => 'Matériel',
                'slug' => 'materiel',
                'description' => 'Équipements et matériel de musculation et fitness',
                'is_active' => true
            ]
        ];

        foreach ($productTypes as $type) {
            ProductType::create($type);
        }
    }
} 