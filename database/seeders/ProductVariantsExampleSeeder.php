<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductVariantsExampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les IDs des attributs
        $tailleId = DB::table('product_attributes')->where('slug', 'taille')->first()->id;
        $couleurId = DB::table('product_attributes')->where('slug', 'couleur')->first()->id;
        $poidsId = DB::table('product_attributes')->where('slug', 'poids')->first()->id;

        // Récupérer les IDs des types de produits
        $vetementTypeId = DB::table('product_types')->where('slug', 'vetements')->first()->id;
        $materielTypeId = DB::table('product_types')->where('slug', 'materiel')->first()->id;
        $proteineTypeId = DB::table('product_types')->where('slug', 'proteines')->first()->id;

        // 1. CRÉER UN PULL AVEC TAILLE ET COULEUR
        $pullId = DB::table('produits')->insertGetId([
            'name' => 'Pull Nike Dri-FIT',
            'image' => 'pull-nike-dri-fit.jpg',
            'description' => 'Pull de sport Nike avec technologie Dri-FIT pour évacuer la transpiration. Parfait pour vos entraînements.',
            'poid' => 300,
            'price' => 35000,
            'user_id' => 1,
            'product_type_id' => $vetementTypeId,
            'stock_quantity' => 0, // Stock géré par les variantes
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Lier le pull aux attributs taille et couleur
        DB::table('product_product_attributes')->insert([
            ['product_id' => $pullId, 'product_attribute_id' => $tailleId, 'is_required' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $pullId, 'product_attribute_id' => $couleurId, 'is_required' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()]
        ]);

        // Créer les variantes du pull
        $pullVariants = [
            ['size' => 'M', 'color' => 'noir', 'price' => 35000, 'stock' => 15],
            ['size' => 'M', 'color' => 'blanc', 'price' => 35000, 'stock' => 12],
            ['size' => 'M', 'color' => 'rouge', 'price' => 37000, 'stock' => 8],
            ['size' => 'L', 'color' => 'noir', 'price' => 35000, 'stock' => 10],
            ['size' => 'L', 'color' => 'blanc', 'price' => 35000, 'stock' => 7],
            ['size' => 'L', 'color' => 'bleu', 'price' => 37000, 'stock' => 5],
            ['size' => 'XL', 'color' => 'noir', 'price' => 38000, 'stock' => 6],
            ['size' => 'XL', 'color' => 'gris', 'price' => 38000, 'stock' => 4],
        ];

        foreach ($pullVariants as $variant) {
            $variantId = DB::table('product_variants')->insertGetId([
                'product_id' => $pullId,
                'sku' => 'PULL-NIKE-' . strtoupper($variant['size']) . '-' . strtoupper($variant['color']),
                'name' => 'Pull Nike Dri-FIT ' . strtoupper($variant['size']) . ' ' . ucfirst($variant['color']),
                'price' => $variant['price'],
                'stock_quantity' => $variant['stock'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Lier la variante aux valeurs d'attributs
            $tailleValueId = DB::table('product_attribute_values')->where('product_attribute_id', $tailleId)->where('value', strtoupper($variant['size']))->first()->id;
            $couleurValueId = DB::table('product_attribute_values')->where('product_attribute_id', $couleurId)->where('value', $variant['color'])->first()->id;

            DB::table('product_variant_attributes')->insert([
                ['product_variant_id' => $variantId, 'product_attribute_value_id' => $tailleValueId, 'created_at' => now(), 'updated_at' => now()],
                ['product_variant_id' => $variantId, 'product_attribute_value_id' => $couleurValueId, 'created_at' => now(), 'updated_at' => now()]
            ]);
        }

        // 2. CRÉER DES HALTÈRES AVEC POIDS
        $haltereId = DB::table('produits')->insertGetId([
            'name' => 'Haltères Professionnels',
            'image' => 'halteres-pro.jpg',
            'description' => 'Haltères de qualité professionnelle avec revêtement anti-dérapant. Idéal pour musculation à domicile.',
            'poid' => 5000,
            'price' => 25000,
            'user_id' => 1,
            'product_type_id' => $materielTypeId,
            'stock_quantity' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Lier les haltères à l'attribut poids
        DB::table('product_product_attributes')->insert([
            'product_id' => $haltereId,
            'product_attribute_id' => $poidsId,
            'is_required' => true,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Créer les variantes d'haltères
        $haltereVariants = [
            ['weight' => '5kg', 'price' => 25000, 'stock' => 20],
            ['weight' => '8kg', 'price' => 35000, 'stock' => 15],
            ['weight' => '10kg', 'price' => 42000, 'stock' => 12],
            ['weight' => '15kg', 'price' => 55000, 'stock' => 8],
            ['weight' => '20kg', 'price' => 68000, 'stock' => 5],
        ];

        foreach ($haltereVariants as $variant) {
            $variantId = DB::table('product_variants')->insertGetId([
                'product_id' => $haltereId,
                'sku' => 'HALTERE-' . strtoupper(str_replace('kg', 'KG', $variant['weight'])),
                'name' => 'Haltères Professionnels ' . $variant['weight'],
                'price' => $variant['price'],
                'stock_quantity' => $variant['stock'],
                'weight' => (int)str_replace('kg', '', $variant['weight']) * 1000, // Convertir en grammes
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Lier la variante à la valeur de poids
            $poidsValueId = DB::table('product_attribute_values')->where('product_attribute_id', $poidsId)->where('value', $variant['weight'])->first()->id;

            DB::table('product_variant_attributes')->insert([
                'product_variant_id' => $variantId,
                'product_attribute_value_id' => $poidsValueId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 3. CRÉER UNE PROTÉINE AVEC POIDS
        $proteineId = DB::table('produits')->insertGetId([
            'name' => 'Whey Protein Gold Standard',
            'image' => 'whey-protein-gold.jpg',
            'description' => 'Protéine whey de haute qualité avec 24g de protéines par portion. Parfait pour la récupération musculaire.',
            'poid' => 1000,
            'price' => 65000,
            'user_id' => 1,
            'product_type_id' => $proteineTypeId,
            'stock_quantity' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Lier la protéine à l'attribut poids
        DB::table('product_product_attributes')->insert([
            'product_id' => $proteineId,
            'product_attribute_id' => $poidsId,
            'is_required' => true,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Créer les variantes de protéine
        $proteineVariants = [
            ['weight' => '1kg', 'price' => 65000, 'stock' => 25],
            ['weight' => '2kg', 'price' => 120000, 'stock' => 18],
            ['weight' => '5kg_protein', 'price' => 280000, 'stock' => 10],
        ];

        foreach ($proteineVariants as $variant) {
            $variantId = DB::table('product_variants')->insertGetId([
                'product_id' => $proteineId,
                'sku' => 'WHEY-GOLD-' . strtoupper(str_replace(['kg', '_protein'], ['KG', ''], $variant['weight'])),
                'name' => 'Whey Protein Gold Standard ' . str_replace('_protein', '', $variant['weight']),
                'price' => $variant['price'],
                'stock_quantity' => $variant['stock'],
                'weight' => (int)str_replace(['kg', '_protein'], ['', ''], $variant['weight']) * 1000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Lier la variante à la valeur de poids
            $poidsValueId = DB::table('product_attribute_values')->where('product_attribute_id', $poidsId)->where('value', $variant['weight'])->first()->id;

            DB::table('product_variant_attributes')->insert([
                'product_variant_id' => $variantId,
                'product_attribute_value_id' => $poidsValueId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 4. CRÉER UN T-SHIRT AVEC TAILLE ET COULEUR
        $tshirtId = DB::table('produits')->insertGetId([
            'name' => 'T-Shirt Under Armour HeatGear',
            'image' => 'tshirt-under-armour.jpg',
            'description' => 'T-shirt de sport Under Armour avec technologie HeatGear. Ultra-léger et respirant.',
            'poid' => 180,
            'price' => 28000,
            'user_id' => 1,
            'product_type_id' => $vetementTypeId,
            'stock_quantity' => 0,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Lier le t-shirt aux attributs taille et couleur
        DB::table('product_product_attributes')->insert([
            ['product_id' => $tshirtId, 'product_attribute_id' => $tailleId, 'is_required' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => $tshirtId, 'product_attribute_id' => $couleurId, 'is_required' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()]
        ]);

        // Créer les variantes du t-shirt
        $tshirtVariants = [
            ['size' => 'S', 'color' => 'noir', 'price' => 28000, 'stock' => 20],
            ['size' => 'S', 'color' => 'blanc', 'price' => 28000, 'stock' => 18],
            ['size' => 'M', 'color' => 'noir', 'price' => 28000, 'stock' => 25],
            ['size' => 'M', 'color' => 'blanc', 'price' => 28000, 'stock' => 22],
            ['size' => 'M', 'color' => 'rouge', 'price' => 30000, 'stock' => 15],
            ['size' => 'L', 'color' => 'noir', 'price' => 28000, 'stock' => 18],
            ['size' => 'L', 'color' => 'bleu', 'price' => 30000, 'stock' => 12],
            ['size' => 'XL', 'color' => 'noir', 'price' => 30000, 'stock' => 10],
        ];

        foreach ($tshirtVariants as $variant) {
            $variantId = DB::table('product_variants')->insertGetId([
                'product_id' => $tshirtId,
                'sku' => 'TSHIRT-UA-' . strtoupper($variant['size']) . '-' . strtoupper($variant['color']),
                'name' => 'T-Shirt Under Armour HeatGear ' . strtoupper($variant['size']) . ' ' . ucfirst($variant['color']),
                'price' => $variant['price'],
                'stock_quantity' => $variant['stock'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Lier la variante aux valeurs d'attributs
            $tailleValueId = DB::table('product_attribute_values')->where('product_attribute_id', $tailleId)->where('value', strtoupper($variant['size']))->first()->id;
            $couleurValueId = DB::table('product_attribute_values')->where('product_attribute_id', $couleurId)->where('value', $variant['color'])->first()->id;

            DB::table('product_variant_attributes')->insert([
                ['product_variant_id' => $variantId, 'product_attribute_value_id' => $tailleValueId, 'created_at' => now(), 'updated_at' => now()],
                ['product_variant_id' => $variantId, 'product_attribute_value_id' => $couleurValueId, 'created_at' => now(), 'updated_at' => now()]
            ]);
        }

        echo "✅ Seeder terminé ! Produits créés avec variantes :\n";
        echo "1. Pull Nike Dri-FIT - 8 variantes (taille + couleur)\n";
        echo "2. Haltères Professionnels - 5 variantes (poids)\n";
        echo "3. Whey Protein Gold Standard - 3 variantes (poids)\n";
        echo "4. T-Shirt Under Armour HeatGear - 8 variantes (taille + couleur)\n";
        echo "Total : 24 variantes créées !\n";
    }
} 