<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductVariantController extends Controller
{
    /**
     * Afficher toutes les variantes d'un produit
     */
    public function index(Produit $produit)
    {
        $variants = $produit->variants()
            ->with(['attributeValues.productAttribute'])
            ->orderBy('sku')
            ->get();

        return response()->json([
            'variants' => $variants
        ], 200);
    }

    /**
     * Créer une nouvelle variante pour un produit
     */
    public function store(Request $request, Produit $produit)
    {
        $this->validate($request, [
            'sku' => 'required|string|max:255|unique:product_variants,sku',
            'name' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'weight' => 'nullable|integer|min:0',
            'barcode' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:25600',
            'is_active' => 'nullable|boolean',
            'attributes' => 'required|array',
            'attributes.*' => 'exists:product_attribute_values,id',
        ]);

        try {
            DB::beginTransaction();

            $variantData = [
                'sku' => $request->sku,
                'name' => $request->name,
                'price' => $request->price,
                'stock_quantity' => $request->stock_quantity,
                'weight' => $request->weight,
                'barcode' => $request->barcode,
                'is_active' => $request->is_active ?? true,
                'track_inventory' => true,
            ];

            if ($request->hasFile('image')) {
                $filename = $request->image->getClientOriginalName();
                $request->image->storeAs('variants', $filename, 'public');
                $variantData['image'] = $filename;
            }

            $variant = $produit->variants()->create($variantData);

            // Associer les valeurs d'attributs
            if (!empty($request->attributes)) {
                $variant->attributeValues()->attach($request->attributes);
            }

            DB::commit();

            return response()->json([
                'message' => 'Variante créée avec succès.',
                'variant' => $variant->load('attributeValues.productAttribute')
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Erreur lors de la création de la variante',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher une variante spécifique
     */
    public function show(Produit $produit, ProductVariant $variant)
    {
        $variant->load(['attributeValues.productAttribute', 'product']);

        return response()->json([
            'variant' => $variant
        ], 200);
    }

    /**
     * Mettre à jour une variante
     */
    public function update(Request $request, Produit $produit, ProductVariant $variant)
    {
        $this->validate($request, [
            'sku' => 'required|string|max:255|unique:product_variants,sku,' . $variant->id,
            'name' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'weight' => 'nullable|integer|min:0',
            'barcode' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:25600',
            'is_active' => 'nullable|boolean',
            'attributes' => 'required|array',
            'attributes.*' => 'exists:product_attribute_values,id',
        ]);

        try {
            DB::beginTransaction();

            $variantData = [
                'sku' => $request->sku,
                'name' => $request->name,
                'price' => $request->price,
                'stock_quantity' => $request->stock_quantity,
                'weight' => $request->weight,
                'barcode' => $request->barcode,
                'is_active' => $request->is_active ?? $variant->is_active,
            ];

            if ($request->hasFile('image')) {
                $filename = $request->image->getClientOriginalName();
                $request->image->storeAs('variants', $filename, 'public');
                $variantData['image'] = $filename;
            }

            $variant->update($variantData);

            // Mettre à jour les valeurs d'attributs
            $variant->attributeValues()->sync($request->attributes);

            DB::commit();

            return response()->json([
                'message' => 'Variante mise à jour avec succès.',
                'variant' => $variant->load('attributeValues.productAttribute')
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Erreur lors de la mise à jour de la variante',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une variante
     */
    public function destroy(Produit $produit, ProductVariant $variant)
    {
        try {
            DB::beginTransaction();

            // Détacher les valeurs d'attributs
            $variant->attributeValues()->detach();
            
            // Supprimer la variante
            $variant->delete();

            DB::commit();

            return response()->json([
                'message' => 'Variante supprimée avec succès.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Erreur lors de la suppression de la variante',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour le stock d'une variante
     */
    public function updateStock(Request $request, Produit $produit, ProductVariant $variant)
    {
        $this->validate($request, [
            'stock_quantity' => 'required|integer|min:0',
        ]);

        $variant->update([
            'stock_quantity' => $request->stock_quantity
        ]);

        return response()->json([
            'message' => 'Stock mis à jour avec succès.',
            'variant' => $variant
        ], 200);
    }

    /**
     * Activer/désactiver une variante
     */
    public function toggleStatus(Produit $produit, ProductVariant $variant)
    {
        $variant->update([
            'is_active' => !$variant->is_active
        ]);

        $status = $variant->is_active ? 'activée' : 'désactivée';

        return response()->json([
            'message' => "Variante {$status} avec succès.",
            'variant' => $variant
        ], 200);
    }
} 