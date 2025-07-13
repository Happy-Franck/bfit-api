<?php

namespace App\Http\Controllers;

use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductTypeController extends Controller
{
    /**
     * Afficher tous les types de produits
     */
    public function index()
    {
        $productTypes = ProductType::active()
            ->withCount('activeProduits')
            ->orderBy('name')
            ->get();

        return response()->json([
            'product_types' => $productTypes
        ], 200);
    }

    /**
     * Créer un nouveau type de produit
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:product_types,name',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'is_active' => $request->is_active ?? true
        ];

        if ($request->hasFile('image')) {
            $filename = $request->image->getClientOriginalName();
            $request->image->storeAs('product_types', $filename, 'public');
            $data['image'] = $filename;
        }

        $productType = ProductType::create($data);

        return response()->json([
            'message' => 'Type de produit créé avec succès.',
            'product_type' => $productType
        ], 201);
    }

    /**
     * Afficher un type de produit spécifique avec ses produits
     */
    public function show(ProductType $productType)
    {
        $productType->load(['activeProduits' => function($query) {
            $query->with('advices')->orderBy('name');
        }]);

        // Calculer la note moyenne pour chaque produit
        $productType->activeProduits->each(function ($produit) {
            $produit->rating = $produit->getAverageRatingAttribute();
        });

        return response()->json([
            'product_type' => $productType
        ], 200);
    }

    /**
     * Mettre à jour un type de produit
     */
    public function update(Request $request, ProductType $productType)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:product_types,name,' . $productType->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        $data = [
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'is_active' => $request->is_active ?? $productType->is_active
        ];

        if ($request->hasFile('image')) {
            $filename = $request->image->getClientOriginalName();
            $request->image->storeAs('product_types', $filename, 'public');
            $data['image'] = $filename;
        }

        $productType->update($data);

        return response()->json([
            'message' => 'Type de produit mis à jour avec succès.',
            'product_type' => $productType
        ], 200);
    }

    /**
     * Supprimer un type de produit
     */
    public function destroy(ProductType $productType)
    {
        // Vérifier si des produits utilisent ce type
        if ($productType->produits()->count() > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer ce type car des produits l\'utilisent encore.'
            ], 422);
        }

        $productType->delete();

        return response()->json([
            'message' => 'Type de produit supprimé avec succès.'
        ], 200);
    }

    /**
     * Activer/désactiver un type de produit
     */
    public function toggleStatus(ProductType $productType)
    {
        $productType->update([
            'is_active' => !$productType->is_active
        ]);

        $status = $productType->is_active ? 'activé' : 'désactivé';

        return response()->json([
            'message' => "Type de produit {$status} avec succès.",
            'product_type' => $productType
        ], 200);
    }

    /**
     * Obtenir les attributs disponibles pour un type de produit
     */
    public function getAttributes(ProductType $productType)
    {
        $attributes = $productType->activeAttributes()
            ->orderBy('product_type_attributes.sort_order')
            ->orderBy('product_attributes.name')
            ->get();

        return response()->json([
            'attributes' => $attributes
        ], 200);
    }
} 