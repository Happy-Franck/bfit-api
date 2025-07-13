<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;

class ProductAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer l'attribut Taille
        $tailleAttribute = ProductAttribute::create([
            'name' => 'Taille',
            'slug' => 'taille',
            'type' => 'select',
            'description' => 'Taille du vêtement',
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 1
        ]);

        // Valeurs pour Taille
        $tailleValues = [
            ['value' => 'XS', 'label' => 'XS', 'sort_order' => 1],
            ['value' => 'S', 'label' => 'S', 'sort_order' => 2],
            ['value' => 'M', 'label' => 'M', 'sort_order' => 3],
            ['value' => 'L', 'label' => 'L', 'sort_order' => 4],
            ['value' => 'XL', 'label' => 'XL', 'sort_order' => 5],
            ['value' => 'XXL', 'label' => 'XXL', 'sort_order' => 6],
        ];

        foreach ($tailleValues as $value) {
            ProductAttributeValue::create([
                'product_attribute_id' => $tailleAttribute->id,
                'value' => $value['value'],
                'label' => $value['label'],
                'price_modifier' => 0,
                'sort_order' => $value['sort_order'],
                'is_active' => true
            ]);
        }

        // Créer l'attribut Couleur
        $couleurAttribute = ProductAttribute::create([
            'name' => 'Couleur',
            'slug' => 'couleur',
            'type' => 'color',
            'description' => 'Couleur du produit',
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 2
        ]);

        // Valeurs pour Couleur
        $couleurValues = [
            ['value' => 'rouge', 'label' => 'Rouge', 'color_code' => '#FF0000', 'sort_order' => 1],
            ['value' => 'bleu', 'label' => 'Bleu', 'color_code' => '#0000FF', 'sort_order' => 2],
            ['value' => 'vert', 'label' => 'Vert', 'color_code' => '#00FF00', 'sort_order' => 3],
            ['value' => 'noir', 'label' => 'Noir', 'color_code' => '#000000', 'sort_order' => 4],
            ['value' => 'blanc', 'label' => 'Blanc', 'color_code' => '#FFFFFF', 'sort_order' => 5],
            ['value' => 'gris', 'label' => 'Gris', 'color_code' => '#808080', 'sort_order' => 6],
        ];

        foreach ($couleurValues as $value) {
            ProductAttributeValue::create([
                'product_attribute_id' => $couleurAttribute->id,
                'value' => $value['value'],
                'label' => $value['label'],
                'color_code' => $value['color_code'],
                'price_modifier' => 0,
                'sort_order' => $value['sort_order'],
                'is_active' => true
            ]);
        }

        // Créer l'attribut Longueur (pour barres de musculation)
        $longueurAttribute = ProductAttribute::create([
            'name' => 'Longueur',
            'slug' => 'longueur',
            'type' => 'select',
            'description' => 'Longueur de la barre en cm',
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 3
        ]);

        // Valeurs pour Longueur
        $longueurValues = [
            ['value' => '120cm', 'label' => '120 cm', 'price_modifier' => 0, 'sort_order' => 1],
            ['value' => '150cm', 'label' => '150 cm', 'price_modifier' => 500, 'sort_order' => 2],
            ['value' => '180cm', 'label' => '180 cm', 'price_modifier' => 1000, 'sort_order' => 3],
            ['value' => '200cm', 'label' => '200 cm', 'price_modifier' => 1500, 'sort_order' => 4],
        ];

        foreach ($longueurValues as $value) {
            ProductAttributeValue::create([
                'product_attribute_id' => $longueurAttribute->id,
                'value' => $value['value'],
                'label' => $value['label'],
                'price_modifier' => $value['price_modifier'],
                'sort_order' => $value['sort_order'],
                'is_active' => true
            ]);
        }

        // Créer l'attribut Poids (pour haltères)
        $poidsAttribute = ProductAttribute::create([
            'name' => 'Poids',
            'slug' => 'poids',
            'type' => 'select',
            'description' => 'Poids en kg',
            'is_required' => true,
            'is_active' => true,
            'sort_order' => 4
        ]);

        // Valeurs pour Poids
        $poidsValues = [
            ['value' => '5kg', 'label' => '5 kg', 'price_modifier' => 0, 'sort_order' => 1],
            ['value' => '10kg', 'label' => '10 kg', 'price_modifier' => 1000, 'sort_order' => 2],
            ['value' => '15kg', 'label' => '15 kg', 'price_modifier' => 2000, 'sort_order' => 3],
            ['value' => '20kg', 'label' => '20 kg', 'price_modifier' => 3000, 'sort_order' => 4],
            ['value' => '25kg', 'label' => '25 kg', 'price_modifier' => 4000, 'sort_order' => 5],
            ['value' => '30kg', 'label' => '30 kg', 'price_modifier' => 5000, 'sort_order' => 6],
        ];

        foreach ($poidsValues as $value) {
            ProductAttributeValue::create([
                'product_attribute_id' => $poidsAttribute->id,
                'value' => $value['value'],
                'label' => $value['label'],
                'price_modifier' => $value['price_modifier'],
                'sort_order' => $value['sort_order'],
                'is_active' => true
            ]);
        }

        // Créer l'attribut Matière
        $matiereAttribute = ProductAttribute::create([
            'name' => 'Matière',
            'slug' => 'matiere',
            'type' => 'select',
            'description' => 'Matière du tissu',
            'is_required' => false,
            'is_active' => true,
            'sort_order' => 5
        ]);

        // Valeurs pour Matière
        $matiereValues = [
            ['value' => 'coton', 'label' => 'Coton', 'price_modifier' => 0, 'sort_order' => 1],
            ['value' => 'polyester', 'label' => 'Polyester', 'price_modifier' => 200, 'sort_order' => 2],
            ['value' => 'lycra', 'label' => 'Lycra', 'price_modifier' => 500, 'sort_order' => 3],
            ['value' => 'bambou', 'label' => 'Bambou', 'price_modifier' => 800, 'sort_order' => 4],
        ];

        foreach ($matiereValues as $value) {
            ProductAttributeValue::create([
                'product_attribute_id' => $matiereAttribute->id,
                'value' => $value['value'],
                'label' => $value['label'],
                'price_modifier' => $value['price_modifier'],
                'sort_order' => $value['sort_order'],
                'is_active' => true
            ]);
        }
    }
} 