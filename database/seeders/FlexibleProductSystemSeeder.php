<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Support\Facades\DB;

class FlexibleProductSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * SYSTÈME FLEXIBLE : Plus de liaisons fixes entre types et attributs
     * Tous les attributs sont disponibles pour tous les types de produits
     */
    public function run(): void
    {
        // Supprimer toutes les liaisons existantes pour repartir à zéro
        DB::table('product_type_attributes')->truncate();
        
        // Créer des attributs universels utilisables par tous les types de produits
        $this->createUniversalAttributes();
        
        echo "✅ Système flexible initialisé !\n";
        echo "📋 Attributs universels créés :\n";
        echo "   - Taille (XS à XXL)\n";
        echo "   - Couleur (avec codes couleur)\n";
        echo "   - Poids (différentes valeurs)\n";
        echo "   - Longueur (pour barres)\n";
        echo "   - Matière (tissus)\n";
        echo "   - Goût (pour suppléments)\n";
        echo "   - Format (pour emballages)\n";
        echo "\n🎯 Flexibilité totale : N'importe quel produit peut utiliser n'importe quel attribut !\n";
    }
    
    private function createUniversalAttributes()
    {
        // 1. TAILLE - Utilisable pour vêtements, équipements, etc.
        $tailleAttribute = ProductAttribute::create([
            'name' => 'Taille',
            'slug' => 'taille',
            'type' => 'select',
            'description' => 'Taille du produit (vêtements, équipements, etc.)',
            'is_required' => false,
            'is_active' => true,
            'sort_order' => 1
        ]);

        $tailleValues = [
            ['value' => 'XS', 'label' => 'XS', 'price_modifier' => 0, 'sort_order' => 1],
            ['value' => 'S', 'label' => 'S', 'price_modifier' => 0, 'sort_order' => 2],
            ['value' => 'M', 'label' => 'M', 'price_modifier' => 0, 'sort_order' => 3],
            ['value' => 'L', 'label' => 'L', 'price_modifier' => 0, 'sort_order' => 4],
            ['value' => 'XL', 'label' => 'XL', 'price_modifier' => 500, 'sort_order' => 5],
            ['value' => 'XXL', 'label' => 'XXL', 'price_modifier' => 1000, 'sort_order' => 6],
        ];

        foreach ($tailleValues as $value) {
            ProductAttributeValue::create([
                'product_attribute_id' => $tailleAttribute->id,
                'value' => $value['value'],
                'label' => $value['label'],
                'price_modifier' => $value['price_modifier'],
                'sort_order' => $value['sort_order'],
                'is_active' => true
            ]);
        }

        // 2. COULEUR - Utilisable pour tout produit
        $couleurAttribute = ProductAttribute::create([
            'name' => 'Couleur',
            'slug' => 'couleur',
            'type' => 'color',
            'description' => 'Couleur du produit',
            'is_required' => false,
            'is_active' => true,
            'sort_order' => 2
        ]);

        $couleurValues = [
            ['value' => 'rouge', 'label' => 'Rouge', 'color_code' => '#FF0000', 'sort_order' => 1],
            ['value' => 'bleu', 'label' => 'Bleu', 'color_code' => '#0000FF', 'sort_order' => 2],
            ['value' => 'vert', 'label' => 'Vert', 'color_code' => '#00FF00', 'sort_order' => 3],
            ['value' => 'noir', 'label' => 'Noir', 'color_code' => '#000000', 'sort_order' => 4],
            ['value' => 'blanc', 'label' => 'Blanc', 'color_code' => '#FFFFFF', 'sort_order' => 5],
            ['value' => 'gris', 'label' => 'Gris', 'color_code' => '#808080', 'sort_order' => 6],
            ['value' => 'rose', 'label' => 'Rose', 'color_code' => '#FFC0CB', 'sort_order' => 7],
            ['value' => 'violet', 'label' => 'Violet', 'color_code' => '#8A2BE2', 'sort_order' => 8],
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

        // 3. POIDS - Utilisable pour haltères, suppléments, etc.
        $poidsAttribute = ProductAttribute::create([
            'name' => 'Poids',
            'slug' => 'poids',
            'type' => 'select',
            'description' => 'Poids du produit',
            'is_required' => false,
            'is_active' => true,
            'sort_order' => 3
        ]);

        $poidsValues = [
            ['value' => '0.5kg', 'label' => '0.5 kg', 'price_modifier' => 0, 'sort_order' => 1],
            ['value' => '1kg', 'label' => '1 kg', 'price_modifier' => 0, 'sort_order' => 2],
            ['value' => '2kg', 'label' => '2 kg', 'price_modifier' => 2000, 'sort_order' => 3],
            ['value' => '2.5kg', 'label' => '2.5 kg', 'price_modifier' => 2500, 'sort_order' => 4],
            ['value' => '5kg', 'label' => '5 kg', 'price_modifier' => 5000, 'sort_order' => 5],
            ['value' => '10kg', 'label' => '10 kg', 'price_modifier' => 10000, 'sort_order' => 6],
            ['value' => '15kg', 'label' => '15 kg', 'price_modifier' => 15000, 'sort_order' => 7],
            ['value' => '20kg', 'label' => '20 kg', 'price_modifier' => 20000, 'sort_order' => 8],
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

        // 4. LONGUEUR - Utilisable pour barres, tapis, etc.
        $longueurAttribute = ProductAttribute::create([
            'name' => 'Longueur',
            'slug' => 'longueur',
            'type' => 'select',
            'description' => 'Longueur du produit',
            'is_required' => false,
            'is_active' => true,
            'sort_order' => 4
        ]);

        $longueurValues = [
            ['value' => '60cm', 'label' => '60 cm', 'price_modifier' => 0, 'sort_order' => 1],
            ['value' => '120cm', 'label' => '120 cm', 'price_modifier' => 500, 'sort_order' => 2],
            ['value' => '150cm', 'label' => '150 cm', 'price_modifier' => 1000, 'sort_order' => 3],
            ['value' => '180cm', 'label' => '180 cm', 'price_modifier' => 1500, 'sort_order' => 4],
            ['value' => '200cm', 'label' => '200 cm', 'price_modifier' => 2000, 'sort_order' => 5],
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

        // 5. MATIÈRE - Utilisable pour vêtements, équipements
        $matiereAttribute = ProductAttribute::create([
            'name' => 'Matière',
            'slug' => 'matiere',
            'type' => 'select',
            'description' => 'Matière du produit',
            'is_required' => false,
            'is_active' => true,
            'sort_order' => 5
        ]);

        $matiereValues = [
            ['value' => 'coton', 'label' => 'Coton', 'price_modifier' => 0, 'sort_order' => 1],
            ['value' => 'polyester', 'label' => 'Polyester', 'price_modifier' => 200, 'sort_order' => 2],
            ['value' => 'lycra', 'label' => 'Lycra', 'price_modifier' => 500, 'sort_order' => 3],
            ['value' => 'bambou', 'label' => 'Bambou', 'price_modifier' => 800, 'sort_order' => 4],
            ['value' => 'acier', 'label' => 'Acier', 'price_modifier' => 1000, 'sort_order' => 5],
            ['value' => 'plastique', 'label' => 'Plastique', 'price_modifier' => 0, 'sort_order' => 6],
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

        // 6. GOÛT - Utilisable pour suppléments
        $goutAttribute = ProductAttribute::create([
            'name' => 'Goût',
            'slug' => 'gout',
            'type' => 'select',
            'description' => 'Goût du supplément',
            'is_required' => false,
            'is_active' => true,
            'sort_order' => 6
        ]);

        $goutValues = [
            ['value' => 'chocolat', 'label' => 'Chocolat', 'price_modifier' => 0, 'sort_order' => 1],
            ['value' => 'vanille', 'label' => 'Vanille', 'price_modifier' => 0, 'sort_order' => 2],
            ['value' => 'fraise', 'label' => 'Fraise', 'price_modifier' => 0, 'sort_order' => 3],
            ['value' => 'banane', 'label' => 'Banane', 'price_modifier' => 0, 'sort_order' => 4],
            ['value' => 'neutre', 'label' => 'Neutre', 'price_modifier' => -200, 'sort_order' => 5],
            ['value' => 'cookies', 'label' => 'Cookies & Cream', 'price_modifier' => 500, 'sort_order' => 6],
        ];

        foreach ($goutValues as $value) {
            ProductAttributeValue::create([
                'product_attribute_id' => $goutAttribute->id,
                'value' => $value['value'],
                'label' => $value['label'],
                'price_modifier' => $value['price_modifier'],
                'sort_order' => $value['sort_order'],
                'is_active' => true
            ]);
        }

        // 7. FORMAT - Utilisable pour emballages
        $formatAttribute = ProductAttribute::create([
            'name' => 'Format',
            'slug' => 'format',
            'type' => 'select',
            'description' => 'Format d\'emballage',
            'is_required' => false,
            'is_active' => true,
            'sort_order' => 7
        ]);

        $formatValues = [
            ['value' => 'sachet', 'label' => 'Sachet', 'price_modifier' => 0, 'sort_order' => 1],
            ['value' => 'pot', 'label' => 'Pot', 'price_modifier' => 500, 'sort_order' => 2],
            ['value' => 'boite', 'label' => 'Boîte', 'price_modifier' => 300, 'sort_order' => 3],
            ['value' => 'tube', 'label' => 'Tube', 'price_modifier' => 200, 'sort_order' => 4],
        ];

        foreach ($formatValues as $value) {
            ProductAttributeValue::create([
                'product_attribute_id' => $formatAttribute->id,
                'value' => $value['value'],
                'label' => $value['label'],
                'price_modifier' => $value['price_modifier'],
                'sort_order' => $value['sort_order'],
                'is_active' => true
            ]);
        }
    }
} 