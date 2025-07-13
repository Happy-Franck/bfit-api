<?php

namespace App\Http\Controllers;

use App\Models\ProductType;
use App\Models\ProductAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductTypeAttributeController extends Controller
{
    /**
     * Récupérer tous les attributs disponibles (plus de restriction par type)
     */
    public function getAttributesByProductType($productTypeId)
    {
        try {
            $productType = ProductType::findOrFail($productTypeId);
            
            // CHANGEMENT MAJEUR : Retourner TOUS les attributs disponibles
            // L'utilisateur peut choisir librement quels attributs utiliser
            $attributes = ProductAttribute::with('activeValues')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return response()->json([
                'product_type' => $productType,
                'attributes' => $attributes->map(function ($attribute) {
                    return [
                        'id' => $attribute->id,
                        'name' => $attribute->name,
                        'slug' => $attribute->slug,
                        'type' => $attribute->type,
                        'description' => $attribute->description,
                        'is_required' => false, // Plus de contrainte - l'utilisateur choisit
                        'is_active' => $attribute->is_active,
                        'sort_order' => $attribute->sort_order,
                        'values' => $attribute->activeValues->map(function ($value) {
                            return [
                                'id' => $value->id,
                                'product_attribute_id' => $value->product_attribute_id,
                                'value' => $value->value,
                                'label' => $value->label,
                                'color_code' => $value->color_code,
                                'price_modifier' => $value->price_modifier,
                                'sort_order' => $value->sort_order,
                                'is_active' => $value->is_active,
                            ];
                        })
                    ];
                })
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des attributs',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lier un attribut à un type de produit (optionnel - pour suggestions)
     */
    public function attachAttribute(Request $request, $productTypeId)
    {
        try {
            $request->validate([
                'attribute_id' => 'required|exists:product_attributes,id',
                'is_required' => 'boolean',
                'sort_order' => 'integer'
            ]);

            $productType = ProductType::findOrFail($productTypeId);
            $attribute = ProductAttribute::findOrFail($request->attribute_id);

            // Vérifier si l'attribut n'est pas déjà lié
            if ($productType->attributes()->where('product_attribute_id', $request->attribute_id)->exists()) {
                return response()->json([
                    'message' => 'Cet attribut est déjà lié à ce type de produit.'
                ], 422);
            }

            // Lier l'attribut au type de produit
            $productType->attributes()->attach($request->attribute_id, [
                'is_required' => $request->is_required ?? false,
                'sort_order' => $request->sort_order ?? 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'message' => 'Attribut lié au type de produit avec succès.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la liaison de l\'attribut',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Détacher un attribut d'un type de produit
     */
    public function detachAttribute($productTypeId, $attributeId)
    {
        try {
            $productType = ProductType::findOrFail($productTypeId);
            $attribute = ProductAttribute::findOrFail($attributeId);

            // Vérifier si l'attribut est lié au type de produit
            if (!$productType->attributes()->where('product_attribute_id', $attributeId)->exists()) {
                return response()->json([
                    'message' => 'Cet attribut n\'est pas lié à ce type de produit.'
                ], 422);
            }

            // Détacher l'attribut du type de produit
            $productType->attributes()->detach($attributeId);

            return response()->json([
                'message' => 'Attribut détaché du type de produit avec succès.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du détachement de l\'attribut',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour les paramètres d'un attribut pour un type de produit
     */
    public function updateAttribute(Request $request, $productTypeId, $attributeId)
    {
        try {
            $request->validate([
                'is_required' => 'boolean',
                'sort_order' => 'integer'
            ]);

            $productType = ProductType::findOrFail($productTypeId);
            $attribute = ProductAttribute::findOrFail($attributeId);

            // Vérifier si l'attribut est lié au type de produit
            if (!$productType->attributes()->where('product_attribute_id', $attributeId)->exists()) {
                return response()->json([
                    'message' => 'Cet attribut n\'est pas lié à ce type de produit.'
                ], 422);
            }

            // Mettre à jour les paramètres de liaison
            $productType->attributes()->updateExistingPivot($attributeId, [
                'is_required' => $request->is_required ?? false,
                'sort_order' => $request->sort_order ?? 0,
                'updated_at' => now()
            ]);

            return response()->json([
                'message' => 'Paramètres de l\'attribut mis à jour avec succès.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour des paramètres',
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 