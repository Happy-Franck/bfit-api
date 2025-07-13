<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ProductType;

class ProduitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les IDs des types de produits
        $proteinType = ProductType::where('slug', 'proteines')->first();
        $creatineType = ProductType::where('slug', 'creatine')->first();
        $gainerType = ProductType::where('slug', 'gainers')->first();
        $fatBurnerType = ProductType::where('slug', 'bruleurs-de-graisse')->first();
        $vitaminType = ProductType::where('slug', 'vitamines-mineraux')->first();
        $barType = ProductType::where('slug', 'barres-energetiques')->first();
        $clothingType = ProductType::where('slug', 'vetements')->first();
        $equipmentType = ProductType::where('slug', 'materiel')->first();

        // ================ PROTÉINES ================
        DB::table('produits')->insert([
            'name' => 'Pure whey isolate',
            'image' => 'pure-whey-isolate.jpg',
            'description' => 'Protéine whey isolat de haute qualité pour la construction musculaire. Absorption rapide et faible en lactose.',
            'poid' => 1000,
            'price' => 55000,
            'user_id' => 1,
            'product_type_id' => $proteinType ? $proteinType->id : null,
            'stock_quantity' => 25,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('produits')->insert([
            'name' => 'Favre iso 100% whey zero',
            'image' => 'favre-iso-whey.jpg',
            'description' => 'Protéine whey sans sucre ajouté, idéale pour la sèche et la définition musculaire.',
            'poid' => 1000,
            'price' => 48000,
            'user_id' => 2,
            'product_type_id' => $proteinType ? $proteinType->id : null,
            'stock_quantity' => 15,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('produits')->insert([
            'name' => 'BCAA Energy',
            'image' => 'bcaa-energy.jpg',
            'description' => 'Acides aminés essentiels pour la récupération et l\'énergie pendant l\'entraînement.',
            'poid' => 500,
            'price' => 35000,
            'user_id' => 1,
            'product_type_id' => $proteinType ? $proteinType->id : null,
            'stock_quantity' => 20,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('produits')->insert([
            'name' => 'Caseine Micellaire',
            'image' => 'caseine-micellaire.jpg',
            'description' => 'Protéine à diffusion lente, parfaite pour la nuit et les longues périodes sans repas.',
            'poid' => 900,
            'price' => 52000,
            'user_id' => 1,
            'product_type_id' => $proteinType ? $proteinType->id : null,
            'stock_quantity' => 12,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // ================ GAINERS ================
        DB::table('produits')->insert([
            'name' => 'Big mass gainer',
            'image' => 'big-mass-gainer.jpg',
            'description' => 'Gainer riche en calories et en protéines pour une prise de masse rapide et efficace.',
            'poid' => 2000,
            'price' => 68000,
            'user_id' => 1,
            'product_type_id' => $gainerType ? $gainerType->id : null,
            'stock_quantity' => 10,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('produits')->insert([
            'name' => 'Mass Gainer Chocolate',
            'image' => 'mass-gainer-chocolate.jpg',
            'description' => 'Gainer saveur chocolat pour une prise de poids contrôlée et un goût délicieux.',
            'poid' => 2500,
            'price' => 75000,
            'user_id' => 2,
            'product_type_id' => $gainerType ? $gainerType->id : null,
            'stock_quantity' => 8,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('produits')->insert([
            'name' => 'Serious Mass',
            'image' => 'serious-mass.jpg',
            'description' => 'Gainer ultra concentré avec plus de 1200 calories par portion. Idéal pour les ectomorphes.',
            'poid' => 2700,
            'price' => 82000,
            'user_id' => 1,
            'product_type_id' => $gainerType ? $gainerType->id : null,
            'stock_quantity' => 6,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // ================ CRÉATINE ================
        DB::table('produits')->insert([
            'name' => 'Creative pro zero',
            'image' => 'creative-pro-zero.jpg',
            'description' => 'Créatine monohydrate pure pour améliorer la force et les performances sportives.',
            'poid' => 300,
            'price' => 42000,
            'user_id' => 2,
            'product_type_id' => $creatineType ? $creatineType->id : null,
            'stock_quantity' => 30,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('produits')->insert([
            'name' => 'Créatine HCL',
            'image' => 'creatine-hcl.jpg',
            'description' => 'Créatine HCL ultra pure, meilleure solubilité et absorption optimisée.',
            'poid' => 250,
            'price' => 38000,
            'user_id' => 1,
            'product_type_id' => $creatineType ? $creatineType->id : null,
            'stock_quantity' => 25,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // ================ BRÛLEURS DE GRAISSE ================
        DB::table('produits')->insert([
            'name' => 'Fat Burner Thermogenic',
            'image' => 'fat-burner-thermo.jpg',
            'description' => 'Brûleur de graisse thermogénique pour accélérer le métabolisme et la perte de poids.',
            'poid' => 120,
            'price' => 45000,
            'user_id' => 1,
            'product_type_id' => $fatBurnerType ? $fatBurnerType->id : null,
            'stock_quantity' => 18,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('produits')->insert([
            'name' => 'L-Carnitine Liquid',
            'image' => 'l-carnitine-liquid.jpg',
            'description' => 'L-Carnitine liquide pour optimiser l\'utilisation des graisses comme source d\'énergie.',
            'poid' => 500,
            'price' => 32000,
            'user_id' => 2,
            'product_type_id' => $fatBurnerType ? $fatBurnerType->id : null,
            'stock_quantity' => 22,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // ================ VITAMINES & MINÉRAUX ================
        DB::table('produits')->insert([
            'name' => 'Multivitamines Sport',
            'image' => 'multivitamines-sport.jpg',
            'description' => 'Complexe multivitaminé spécialement formulé pour les sportifs et athlètes.',
            'poid' => 150,
            'price' => 28000,
            'user_id' => 1,
            'product_type_id' => $vitaminType ? $vitaminType->id : null,
            'stock_quantity' => 35,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('produits')->insert([
            'name' => 'ZMA (Zinc Magnésium)',
            'image' => 'zma-zinc-magnesium.jpg',
            'description' => 'Complexe ZMA pour améliorer la récupération et la qualité du sommeil.',
            'poid' => 90,
            'price' => 25000,
            'user_id' => 2,
            'product_type_id' => $vitaminType ? $vitaminType->id : null,
            'stock_quantity' => 40,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // ================ BARRES ÉNERGÉTIQUES ================
        DB::table('produits')->insert([
            'name' => 'Protein Bar Chocolat',
            'image' => 'protein-bar-chocolat.jpg',
            'description' => 'Barre protéinée saveur chocolat, parfaite pour les collations post-entraînement.',
            'poid' => 60,
            'price' => 3500,
            'user_id' => 1,
            'product_type_id' => $barType ? $barType->id : null,
            'stock_quantity' => 100,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('produits')->insert([
            'name' => 'Energy Bar Fruits',
            'image' => 'energy-bar-fruits.jpg',
            'description' => 'Barre énergétique aux fruits naturels pour un boost d\'énergie avant l\'entraînement.',
            'poid' => 45,
            'price' => 2800,
            'user_id' => 2,
            'product_type_id' => $barType ? $barType->id : null,
            'stock_quantity' => 150,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // ================ VÊTEMENTS ================
        DB::table('produits')->insert([
            'name' => 'T-shirt de Sport Nike',
            'image' => 'tshirt-sport-nike.jpg',
            'description' => 'T-shirt de sport respirant en tissu Dri-FIT pour un confort optimal pendant l\'entraînement.',
            'poid' => 200,
            'price' => 35000,
            'user_id' => 1,
            'product_type_id' => $clothingType ? $clothingType->id : null,
            'stock_quantity' => 50,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('produits')->insert([
            'name' => 'Short de Musculation',
            'image' => 'short-musculation.jpg',
            'description' => 'Short de musculation flexible et résistant, idéal pour tous types d\'entraînements.',
            'poid' => 180,
            'price' => 28000,
            'user_id' => 2,
            'product_type_id' => $clothingType ? $clothingType->id : null,
            'stock_quantity' => 40,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('produits')->insert([
            'name' => 'Gants de Musculation',
            'image' => 'gants-musculation.jpg',
            'description' => 'Gants de musculation avec protection des paumes et grip anti-dérapant.',
            'poid' => 150,
            'price' => 15000,
            'user_id' => 1,
            'product_type_id' => $clothingType ? $clothingType->id : null,
            'stock_quantity' => 60,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('produits')->insert([
            'name' => 'Débardeur Fitness Femme',
            'image' => 'debardeur-fitness-femme.jpg',
            'description' => 'Débardeur fitness pour femme, coupe ajustée et tissu stretch pour une liberté de mouvement.',
            'poid' => 120,
            'price' => 22000,
            'user_id' => 2,
            'product_type_id' => $clothingType ? $clothingType->id : null,
            'stock_quantity' => 35,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('produits')->insert([
            'name' => 'Legging Sport Taille Haute',
            'image' => 'legging-sport-taille-haute.jpg',
            'description' => 'Legging de sport taille haute avec compression pour un maintien optimal pendant l\'effort.',
            'poid' => 250,
            'price' => 32000,
            'user_id' => 1,
            'product_type_id' => $clothingType ? $clothingType->id : null,
            'stock_quantity' => 45,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // ================ MATÉRIEL ================
        DB::table('produits')->insert([
            'name' => 'Haltères Réglables 20kg',
            'image' => 'halteres-reglables-20kg.jpg',
            'description' => 'Paire d\'haltères réglables de 2,5kg à 20kg chacun, parfait pour l\'entraînement à domicile.',
            'poid' => 40000,
            'price' => 125000,
            'user_id' => 1,
            'product_type_id' => $equipmentType ? $equipmentType->id : null,
            'stock_quantity' => 8,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('produits')->insert([
            'name' => 'Tapis de Yoga Premium',
            'image' => 'tapis-yoga-premium.jpg',
            'description' => 'Tapis de yoga antidérapant, épaisseur 6mm, idéal pour yoga, pilates et étirements.',
            'poid' => 1200,
            'price' => 18000,
            'user_id' => 2,
            'product_type_id' => $equipmentType ? $equipmentType->id : null,
            'stock_quantity' => 25,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('produits')->insert([
            'name' => 'Bande Élastique Résistance',
            'image' => 'bande-elastique-resistance.jpg',
            'description' => 'Set de 5 bandes élastiques de résistance différente pour musculation et rééducation.',
            'poid' => 500,
            'price' => 12000,
            'user_id' => 1,
            'product_type_id' => $equipmentType ? $equipmentType->id : null,
            'stock_quantity' => 75,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('produits')->insert([
            'name' => 'Kettlebell 16kg',
            'image' => 'kettlebell-16kg.jpg',
            'description' => 'Kettlebell en fonte de 16kg pour entraînement fonctionnel et cardio-training.',
            'poid' => 16000,
            'price' => 45000,
            'user_id' => 2,
            'product_type_id' => $equipmentType ? $equipmentType->id : null,
            'stock_quantity' => 15,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('produits')->insert([
            'name' => 'Corde à Sauter Pro',
            'image' => 'corde-sauter-pro.jpg',
            'description' => 'Corde à sauter professionnelle avec poignées ergonomiques et câble ajustable.',
            'poid' => 300,
            'price' => 8500,
            'user_id' => 1,
            'product_type_id' => $equipmentType ? $equipmentType->id : null,
            'stock_quantity' => 90,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('produits')->insert([
            'name' => 'Banc de Musculation Pliable',
            'image' => 'banc-musculation-pliable.jpg',
            'description' => 'Banc de musculation multifonction pliable, inclinable et déclinable.',
            'poid' => 25000,
            'price' => 85000,
            'user_id' => 2,
            'product_type_id' => $equipmentType ? $equipmentType->id : null,
            'stock_quantity' => 5,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('produits')->insert([
            'name' => 'Roue Abdominale Pro',
            'image' => 'roue-abdominale-pro.jpg',
            'description' => 'Roue abdominale professionnelle avec double roue et poignées antidérapantes.',
            'poid' => 800,
            'price' => 15000,
            'user_id' => 1,
            'product_type_id' => $equipmentType ? $equipmentType->id : null,
            'stock_quantity' => 30,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
