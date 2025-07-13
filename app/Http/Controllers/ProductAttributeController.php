<?php

namespace App\Http\Controllers;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductAttributeController extends Controller
{
    /**
     * Afficher tous les attributs
     */
    public function index()
    {
        $attributes = ProductAttribute::with('activeValues')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json($attributes, 200);
    }

    /**
     * Créer un nouvel attribut
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:product_attributes,name',
            'type' => 'required|in:text,number,select,color,boolean',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'is_active' => 'boolean'
        ]);

        $attribute = ProductAttribute::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
            'description' => $request->description,
            'is_required' => $request->is_required ?? false,
            'is_active' => $request->is_active ?? true,
            'sort_order' => $request->sort_order ?? 0
        ]);

        return response()->json([
            'message' => 'Attribut créé avec succès.',
            'attribute' => $attribute->load('activeValues')
        ], 201);
    }

    /**
     * Afficher un attribut spécifique
     */
    public function show($id)
    {
        $attribute = ProductAttribute::with('activeValues')->findOrFail($id);

        return response()->json($attribute, 200);
    }

    /**
     * Mettre à jour un attribut
     */
    public function update(Request $request, $id)
    {
        $attribute = ProductAttribute::findOrFail($id);

        $this->validate($request, [
            'name' => 'required|string|max:255|unique:product_attributes,name,' . $attribute->id,
            'type' => 'required|in:text,number,select,color,boolean',
            'description' => 'nullable|string',
            'is_required' => 'boolean',
            'is_active' => 'boolean'
        ]);

        $attribute->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'type' => $request->type,
            'description' => $request->description,
            'is_required' => $request->is_required ?? $attribute->is_required,
            'is_active' => $request->is_active ?? $attribute->is_active,
            'sort_order' => $request->sort_order ?? $attribute->sort_order
        ]);

        return response()->json([
            'message' => 'Attribut mis à jour avec succès.',
            'attribute' => $attribute->load('activeValues')
        ], 200);
    }

    /**
     * Supprimer un attribut
     */
    public function destroy($id)
    {
        $attribute = ProductAttribute::findOrFail($id);

        // Vérifier si l'attribut est utilisé par des produits
        if ($attribute->isUsedByProducts()) {
            return response()->json([
                'message' => 'Impossible de supprimer cet attribut car il est utilisé par des produits.'
            ], 422);
        }

        $attribute->delete();

        return response()->json([
            'message' => 'Attribut supprimé avec succès.'
        ], 200);
    }

    /**
     * Créer une valeur pour un attribut
     */
    public function storeValue(Request $request, $attributeId)
    {
        $attribute = ProductAttribute::findOrFail($attributeId);

        $this->validate($request, [
            'value' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'color_code' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'price_modifier' => 'nullable|numeric',
            'sort_order' => 'nullable|integer'
        ]);

        // Vérifier l'unicité de la valeur pour cet attribut
        if ($attribute->values()->where('value', $request->value)->exists()) {
            return response()->json([
                'message' => 'Cette valeur existe déjà pour cet attribut.'
            ], 422);
        }

        $value = ProductAttributeValue::create([
            'product_attribute_id' => $attributeId,
            'value' => $request->value,
            'label' => $request->label,
            'color_code' => $request->color_code,
            'price_modifier' => $request->price_modifier ?? 0,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => true
        ]);

        return response()->json([
            'message' => 'Valeur d\'attribut créée avec succès.',
            'value' => $value
        ], 201);
    }

    /**
     * Mettre à jour une valeur d'attribut
     */
    public function updateValue(Request $request, $attributeId, $valueId)
    {
        $attribute = ProductAttribute::findOrFail($attributeId);
        $value = ProductAttributeValue::where('product_attribute_id', $attributeId)
            ->findOrFail($valueId);

        $this->validate($request, [
            'value' => 'required|string|max:255',
            'label' => 'required|string|max:255',
            'color_code' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'price_modifier' => 'nullable|numeric',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);

        // Vérifier l'unicité de la valeur pour cet attribut (sauf pour la valeur actuelle)
        if ($attribute->values()->where('value', $request->value)->where('id', '!=', $valueId)->exists()) {
            return response()->json([
                'message' => 'Cette valeur existe déjà pour cet attribut.'
            ], 422);
        }

        $value->update([
            'value' => $request->value,
            'label' => $request->label,
            'color_code' => $request->color_code,
            'price_modifier' => $request->price_modifier ?? $value->price_modifier,
            'sort_order' => $request->sort_order ?? $value->sort_order,
            'is_active' => $request->is_active ?? $value->is_active
        ]);

        return response()->json([
            'message' => 'Valeur d\'attribut mise à jour avec succès.',
            'value' => $value
        ], 200);
    }

    /**
     * Supprimer une valeur d'attribut
     */
    public function destroyValue($attributeId, $valueId)
    {
        $attribute = ProductAttribute::findOrFail($attributeId);
        $value = ProductAttributeValue::where('product_attribute_id', $attributeId)
            ->findOrFail($valueId);

        // Vérifier si la valeur est utilisée par des variantes
        if ($value->isUsedByVariants()) {
            return response()->json([
                'message' => 'Impossible de supprimer cette valeur car elle est utilisée par des variantes de produits.'
            ], 422);
        }

        $value->delete();

        return response()->json([
            'message' => 'Valeur d\'attribut supprimée avec succès.'
        ], 200);
    }

    /**
     * Obtenir tous les attributs avec leurs valeurs (pour la gestion flexible)
     */
    public function getAllWithValues()
    {
        $attributes = ProductAttribute::with('activeValues')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return response()->json([
            'attributes' => $attributes->map(function ($attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'slug' => $attribute->slug,
                    'type' => $attribute->type,
                    'description' => $attribute->description,
                    'is_required' => $attribute->is_required,
                    'is_active' => $attribute->is_active,
                    'sort_order' => $attribute->sort_order,
                    'values' => $attribute->activeValues->map(function ($value) {
                        return [
                            'id' => $value->id,
                            'value' => $value->value,
                            'label' => $value->label,
                            'color_code' => $value->color_code,
                            'price_modifier' => $value->price_modifier,
                            'sort_order' => $value->sort_order,
                            'is_active' => $value->is_active,
                            'is_used_by_variants' => $value->isUsedByVariants(),
                        ];
                    })
                ];
            })
        ], 200);
    }
} 