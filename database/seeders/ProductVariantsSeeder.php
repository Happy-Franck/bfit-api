<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductVariantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les attributs
        $tailleId = DB::table('product_attributes')->insertGetId([
            'name' => 'Taille',
            'slug' => 'taille',
            'type' => 'select',
            'description' => 'Taille du vêtement',
            'is_required' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $couleurId = DB::table('product_attributes')->insertGetId([
            'name' => 'Couleur',
            'slug' => 'couleur',
            'type' => 'color',
            'description' => 'Couleur du produit',
            'is_required' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $poidsId = DB::table('product_attributes')->insertGetId([
            'name' => 'Poids',
            'slug' => 'poids',
            'type' => 'select',
            'description' => 'Poids du produit',
            'is_required' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Valeurs pour taille
        $tailleValues = [
            ['value' => 'XS', 'label' => 'Extra Small', 'sort_order' => 1],
            ['value' => 'S', 'label' => 'Small', 'sort_order' => 2],
            ['value' => 'M', 'label' => 'Medium', 'sort_order' => 3],
            ['value' => 'L', 'label' => 'Large', 'sort_order' => 4],
            ['value' => 'XL', 'label' => 'Extra Large', 'sort_order' => 5],
            ['value' => 'XXL', 'label' => 'Double Extra Large', 'sort_order' => 6],
        ];

        foreach ($tailleValues as $value) {
            DB::table('product_attribute_values')->insert([
                'product_attribute_id' => $tailleId,
                'value' => $value['value'],
                'label' => $value['label'],
                'sort_order' => $value['sort_order'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Valeurs pour couleur
        $couleurValues = [
            ['value' => 'noir', 'label' => 'Noir', 'color_code' => '#000000'],
            ['value' => 'blanc', 'label' => 'Blanc', 'color_code' => '#FFFFFF'],
            ['value' => 'rouge', 'label' => 'Rouge', 'color_code' => '#FF0000'],
            ['value' => 'bleu', 'label' => 'Bleu', 'color_code' => '#0000FF'],
            ['value' => 'vert', 'label' => 'Vert', 'color_code' => '#00FF00'],
            ['value' => 'gris', 'label' => 'Gris', 'color_code' => '#808080'],
        ];

        foreach ($couleurValues as $value) {
            DB::table('product_attribute_values')->insert([
                'product_attribute_id' => $couleurId,
                'value' => $value['value'],
                'label' => $value['label'],
                'color_code' => $value['color_code'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Valeurs pour poids
        $poidsValues = [
            ['value' => '5kg', 'label' => '5 kilogrammes', 'price_modifier' => 0],
            ['value' => '8kg', 'label' => '8 kilogrammes', 'price_modifier' => 5000],
            ['value' => '10kg', 'label' => '10 kilogrammes', 'price_modifier' => 8000],
            ['value' => '15kg', 'label' => '15 kilogrammes', 'price_modifier' => 12000],
            ['value' => '20kg', 'label' => '20 kilogrammes', 'price_modifier' => 15000],
            ['value' => '1kg', 'label' => '1 kilogramme', 'price_modifier' => 0],
            ['value' => '2kg', 'label' => '2 kilogrammes', 'price_modifier' => 2000],
            ['value' => '5kg_protein', 'label' => '5 kilogrammes', 'price_modifier' => 10000],
        ];

        foreach ($poidsValues as $value) {
            DB::table('product_attribute_values')->insert([
                'product_attribute_id' => $poidsId,
                'value' => $value['value'],
                'label' => $value['label'],
                'price_modifier' => $value['price_modifier'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Exemple : Créer un produit Pull et ses variantes
        // (vous devez d'abord avoir des produits dans la table produits)
        
        echo "Seeder terminé ! Vous pouvez maintenant :\n";
        echo "1. Lier les produits aux attributs via product_product_attributes\n";
        echo "2. Créer des variantes dans product_variants\n";
        echo "3. Lier les variantes aux valeurs d'attributs via product_variant_attributes\n";
    }
} 