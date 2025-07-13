<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductType;
use App\Models\ProductAttribute;
use Illuminate\Support\Facades\DB;

class ProductTypeAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les types de produits
        $vetementType = ProductType::where('slug', 'vetements')->first();
        $materielType = ProductType::where('slug', 'materiel')->first();

        // Récupérer les attributs
        $tailleAttribute = ProductAttribute::where('slug', 'taille')->first();
        $couleurAttribute = ProductAttribute::where('slug', 'couleur')->first();
        $longueurAttribute = ProductAttribute::where('slug', 'longueur')->first();
        $poidsAttribute = ProductAttribute::where('slug', 'poids')->first();
        $matiereAttribute = ProductAttribute::where('slug', 'matiere')->first();

        // Lier les attributs aux types de produits
        if ($vetementType) {
            // Vêtements : Taille, Couleur, Matière
            if ($tailleAttribute) {
                DB::table('product_type_attributes')->insert([
                    'product_type_id' => $vetementType->id,
                    'product_attribute_id' => $tailleAttribute->id,
                    'is_required' => true,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            if ($couleurAttribute) {
                DB::table('product_type_attributes')->insert([
                    'product_type_id' => $vetementType->id,
                    'product_attribute_id' => $couleurAttribute->id,
                    'is_required' => true,
                    'sort_order' => 2,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            if ($matiereAttribute) {
                DB::table('product_type_attributes')->insert([
                    'product_type_id' => $vetementType->id,
                    'product_attribute_id' => $matiereAttribute->id,
                    'is_required' => false,
                    'sort_order' => 3,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        if ($materielType) {
            // Matériel : Longueur, Poids, Couleur
            if ($longueurAttribute) {
                DB::table('product_type_attributes')->insert([
                    'product_type_id' => $materielType->id,
                    'product_attribute_id' => $longueurAttribute->id,
                    'is_required' => true,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            if ($poidsAttribute) {
                DB::table('product_type_attributes')->insert([
                    'product_type_id' => $materielType->id,
                    'product_attribute_id' => $poidsAttribute->id,
                    'is_required' => true,
                    'sort_order' => 2,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            if ($couleurAttribute) {
                DB::table('product_type_attributes')->insert([
                    'product_type_id' => $materielType->id,
                    'product_attribute_id' => $couleurAttribute->id,
                    'is_required' => false,
                    'sort_order' => 3,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
} 