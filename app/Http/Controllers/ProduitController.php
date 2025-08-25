<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use App\Models\ProductType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Produit::query()
                ->select([
                    'id', 'name', 'image', 'description', 'poid', 'price', 
                    'product_type_id', 'stock_quantity', 'is_active', 
                    'created_at', 'updated_at'
                ])
                ->withCount(['advices as comments_count']);

            // Recherche par nom
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where('name', 'LIKE', "%{$searchTerm}%");
            }

            // Filtrage par prix
            $minPrice = $request->get('min_price');
            $maxPrice = $request->get('max_price');
            if ($minPrice !== null && $minPrice !== '') {
                $query->where('price', '>=', (float) $minPrice);
            }
            if ($maxPrice !== null && $maxPrice !== '') {
                $query->where('price', '<=', (float) $maxPrice);
            }

            // Tri
            $sortBy = $request->get('sort_by', 'id');
            $sortOrder = $request->get('sort_order', 'desc');
            
            // Validation des colonnes de tri autorisées
            $allowedSortColumns = ['id', 'name', 'price', 'stock_quantity', 'is_active', 'product_type_id', 'created_at'];
            if (!in_array($sortBy, $allowedSortColumns)) {
                $sortBy = 'id';
            }
            
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $produits = $query->paginate($perPage);

            // Charger les relations après pagination
            $produits->load('productType');

            // Ajouter les données supplémentaires
            foreach ($produits as $produit) {
                // Calculer la note moyenne
                $produit->rating = $produit->advices()->avg('note') ?? 0;
            }

            return response()->json([
                'data' => $produits->items(),
                'current_page' => $produits->currentPage(),
                'last_page' => $produits->lastPage(),
                'per_page' => $produits->perPage(),
                'total' => $produits->total(),
                'pagination' => [
                    'current_page' => $produits->currentPage(),
                    'last_page' => $produits->lastPage(),
                    'per_page' => $produits->perPage(),
                    'total' => $produits->total()
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des produits',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }
    public function indexChallenger(Request $request)
    {
        try {
            // Récupérer tous les types de produits avec le nombre de produits pour chaque type
            $productTypes = \App\Models\ProductType::withCount('produits')
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            // Calculer le total de tous les produits (sans filtre)
            $totalProductsCount = Produit::count();

            $query = Produit::query()
                ->select([
                    'id', 'name', 'image', 'description', 'poid', 'price', 
                    'product_type_id', 'stock_quantity', 'is_active', 
                    'created_at', 'updated_at'
                ])
                ->withCount(['advices as comments_count']);

            // Recherche par nom
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where('name', 'LIKE', "%{$searchTerm}%");
            }

            // Filtrage par type de produit
            if ($request->filled('product_type_id')) {
                $query->where('product_type_id', $request->product_type_id);
            }

            // Filtrage par prix
            $minPrice = $request->get('min_price');
            $maxPrice = $request->get('max_price');
            if ($minPrice !== null && $minPrice !== '') {
                $query->where('price', '>=', (float) $minPrice);
            }
            if ($maxPrice !== null && $maxPrice !== '') {
                $query->where('price', '<=', (float) $maxPrice);
            }

            // Tri
            $sortBy = $request->get('sort_by', 'id');
            $sortOrder = $request->get('sort_order', 'desc');
            
            // Validation des colonnes de tri autorisées
            $allowedSortColumns = ['id', 'name', 'price', 'stock_quantity', 'is_active', 'product_type_id', 'created_at'];
            if (!in_array($sortBy, $allowedSortColumns)) {
                $sortBy = 'id';
            }
            
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $produits = $query->paginate($perPage);

            // Charger les relations après pagination
            $produits->load('productType');

            // Ajouter les données supplémentaires
            foreach ($produits as $produit) {
                // Calculer la note moyenne
                $produit->rating = $produit->advices()->avg('note') ?? 0;
            }

            $response = [
                'data' => $produits->items(),
                'current_page' => $produits->currentPage(),
                'last_page' => $produits->lastPage(),
                'per_page' => $produits->perPage(),
                'total' => $produits->total(),
                'total_products_count' => $totalProductsCount, // Total de tous les produits
                'pagination' => [
                    'current_page' => $produits->currentPage(),
                    'last_page' => $produits->lastPage(),
                    'per_page' => $produits->perPage(),
                    'total' => $produits->total()
                ],
                'product_types' => $productTypes
            ];

            return response()->json($response, 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des produits',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Décoder les chaînes JSON si nécessaire
        $attributes = $request->attributes;
        if (is_string($attributes)) {
            $decoded = json_decode($attributes, true);
            $attributes = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
        } elseif (!is_array($attributes)) {
            $attributes = [];
        }
        
        $variants = $request->variants;
        if (is_string($variants)) {
            $decoded = json_decode($variants, true);
            $variants = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
        } elseif (!is_array($variants)) {
            $variants = [];
        }

        // Déterminer si c'est un produit simple ou avec variantes
        $hasVariants = !empty($variants) && is_array($variants) && count($variants) > 0;

        $rules = [
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:25600',
            'description' => 'required|string',
            'product_type_id' => 'required|exists:product_types,id',
            'is_active' => 'nullable|in:true,false,1,0',
        ];

        // Règles conditionnelles selon le type de produit
        if ($hasVariants) {
            // Produit avec variantes : poids et stock gérés par les variantes
            $rules['poid'] = 'nullable|numeric|min:0';
            $rules['price'] = 'required|numeric|min:0'; // Prix de base
            $rules['stock_quantity'] = 'nullable|integer|min:0'; // Sera ignoré, calculé depuis les variantes
            
            // Règles pour les variantes
            $rules['variants'] = 'required|array|min:1';
            $rules['variants.*.sku'] = 'required|string|max:255|unique:product_variants,sku';
            $rules['variants.*.name'] = 'nullable|string|max:255';
            $rules['variants.*.price'] = 'required|numeric|min:0';
            $rules['variants.*.stock_quantity'] = 'required|integer|min:0';
            $rules['variants.*.barcode'] = 'nullable|string|max:255';
            $rules['variants.*.is_active'] = 'nullable|boolean';
            $rules['variants.*.attributes'] = 'required|array';
            $rules['variants.*.attributes.*'] = 'exists:product_attribute_values,id';
            
            // Règles pour les attributs
            $rules['attributes'] = 'nullable|array';
            $rules['attributes.*'] = 'exists:product_attributes,id';
        } else {
            // Produit simple : poids et stock requis
            $rules['poid'] = 'required|numeric|min:0';
            $rules['price'] = 'required|numeric|min:0';
            $rules['stock_quantity'] = 'required|integer|min:0';
        }

        // Créer un tableau de données modifié pour la validation
        $validationData = $request->all();
        $validationData['attributes'] = $attributes;
        $validationData['variants'] = $variants;
        
        // Validation avec les données modifiées
        $validator = \Illuminate\Support\Facades\Validator::make($validationData, $rules);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Convertir is_active en booléen
            $isActive = $request->is_active;
            if ($isActive === 'true' || $isActive === '1') {
                $isActive = true;
            } elseif ($isActive === 'false' || $isActive === '0') {
                $isActive = false;
            } else {
                $isActive = true;
            }

            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'poid' => $request->poid ?? 0,
                'price' => $request->price,
                'product_type_id' => $request->product_type_id,
                'is_active' => $isActive,
                'user_id' => Auth::user()->id,
            ];

            // Gestion du stock selon le type de produit
            if ($hasVariants) {
                // Pour les produits avec variantes, calculer le stock total
                $totalStock = 0;
                foreach ($variants as $variantData) {
                    $totalStock += (int)$variantData['stock_quantity'];
                }
                $data['stock_quantity'] = $totalStock;
            } else {
                // Pour les produits simples, utiliser le stock fourni
                $data['stock_quantity'] = $request->stock_quantity;
            }

            if ($request->hasFile('image')) {
                $filename = $request->image->getClientOriginalName();
                $request->image->storeAs('produits', $filename, 'public');
                $data['image'] = $filename;
            }

            $produit = Produit::create($data);

            // Gestion des attributs (seulement si le produit a des variantes)
            if ($hasVariants && !empty($attributes) && is_array($attributes)) {
                $attributesData = [];
                foreach ($attributes as $index => $attributeId) {
                    $attributesData[$attributeId] = [
                        'is_required' => true,
                        'sort_order' => $index,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                $produit->attributes()->attach($attributesData);
            }

            // Gestion des variantes
            if ($hasVariants) {
                foreach ($variants as $index => $variantData) {
                    // Créer la variante
                    $variant = $produit->variants()->create([
                        'sku' => $variantData['sku'],
                        'name' => $variantData['name'] ?? null,
                        'price' => $variantData['price'],
                        'stock_quantity' => $variantData['stock_quantity'],
                        'barcode' => $variantData['barcode'] ?? null,
                        'is_active' => $variantData['is_active'] ?? true,
                        'track_inventory' => true,
                    ]);

                    // Gestion de l'image de la variante
                    $imageFound = false;
                    
                    // Vérifier avec underscore (format réel envoyé par le frontend)
                    if ($request->hasFile("variant_images_{$index}")) {
                        $variantImage = $request->file("variant_images_{$index}");
                        $originalName = $variantImage->getClientOriginalName();
                        $extension = $variantImage->getClientOriginalExtension();
                        $fileName = pathinfo($originalName, PATHINFO_FILENAME);
                        
                        // Créer un nom de fichier unique pour éviter les conflits
                        $variantImageName = $fileName . '_' . time() . '_' . uniqid() . '.' . $extension;
                        
                        // Stocker l'image dans le dossier variants
                        $variantImage->storeAs('variants', $variantImageName, 'public');
                        
                        // Mettre à jour la variante avec le nom de l'image
                        $variant->update(['image' => $variantImageName]);
                        $imageFound = true;
                    }
                    // Fallback: vérifier aussi avec le point (au cas où)
                    elseif ($request->hasFile("variant_images.{$index}")) {
                        $variantImage = $request->file("variant_images.{$index}");
                        $originalName = $variantImage->getClientOriginalName();
                        $extension = $variantImage->getClientOriginalExtension();
                        $fileName = pathinfo($originalName, PATHINFO_FILENAME);
                        
                        // Créer un nom de fichier unique pour éviter les conflits
                        $variantImageName = $fileName . '_' . time() . '_' . uniqid() . '.' . $extension;
                        
                        // Stocker l'image dans le dossier variants
                        $variantImage->storeAs('variants', $variantImageName, 'public');
                        
                        // Mettre à jour la variante avec le nom de l'image
                        $variant->update(['image' => $variantImageName]);
                        $imageFound = true;
                    }
                    
                    if (!$imageFound) {
                        // \Log::info("Aucune image trouvée pour la variante {$index}"); // Removed debug log
                    }

                    // Associer les valeurs d'attributs à la variante
                    if (!empty($variantData['attributes'])) {
                        $attributeValues = [];
                        foreach ($variantData['attributes'] as $attributeId => $valueId) {
                            $attributeValues[] = $valueId;
                        }
                        $variant->attributeValues()->attach($attributeValues);
                    }
                }
            }

            DB::commit();

            $responseMessage = $hasVariants 
                ? "Le produit avec variantes a bien été créé. Stock total : {$data['stock_quantity']} unités." 
                : 'Le produit simple a bien été créé.';

            return response()->json([
                'message' => $responseMessage,
                'produit' => $produit->load(['productType', 'variants.attributeValues.productAttribute', 'attributes']),
                'has_variants' => $hasVariants,
                'variants_count' => $hasVariants ? count($variants) : 0,
                'total_stock' => $data['stock_quantity']
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Erreur lors de la création du produit',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Produit $produit)
    {
        // Charger toutes les relations nécessaires
        $produit->load([
            'productType',
            'user',
            'advices.user',
            'variants.attributeValues.productAttribute',
            'attributes.activeValues'
        ]);

        // Calculer des données supplémentaires
        $hasVariants = $produit->hasVariants();
        $variantsCount = $produit->variants()->count();
        $activeVariantsCount = $produit->activeVariants()->count();
        $availableVariantsCount = $produit->availableVariants()->count();
        $totalStock = $produit->getTotalStockAttribute();
        $priceRange = $produit->getPriceRangeAttribute();

        // Structure de base des données
        $data = [
            // Informations principales du produit
            'id' => $produit->id,
            'name' => $produit->name,
            'description' => $produit->description,
            'image' => $produit->image,
            'poid' => $produit->poid,
            'price' => $produit->price,
            'stock_quantity' => $produit->stock_quantity,
            'is_active' => $produit->is_active,
            'created_at' => $produit->created_at,
            'updated_at' => $produit->updated_at,
            
            // Relations
            'product_type_id' => $produit->product_type_id,
            'product_type' => $produit->productType ? [
                'id' => $produit->productType->id,
                'name' => $produit->productType->name,
                'slug' => $produit->productType->slug,
                'description' => $produit->productType->description,
                'is_active' => $produit->productType->is_active,
            ] : null,
            
            'user_id' => $produit->user_id,
            'user' => $produit->user ? [
                'id' => $produit->user->id,
                'name' => $produit->user->name,
                'email' => $produit->user->email,
            ] : null,
            
            // Données calculées
            'note' => $produit->getAverageRatingAttribute(),
            'has_variants' => $hasVariants,
            'variants_count' => $variantsCount,
            'active_variants_count' => $activeVariantsCount,
            'available_variants_count' => $availableVariantsCount,
            'total_stock' => $totalStock,
            'price_range' => $priceRange,
            'formatted_price' => $produit->getFormattedPriceAttribute(),
            'is_in_stock' => $produit->isInStock(),
            'can_be_ordered' => $produit->canBeOrdered(),
            'effective_stock' => $produit->getEffectiveStockAttribute(),
        ];

        // Avis clients
        $data['avis'] = $produit->advices->map(function ($advice) {
            return [
                'id' => $advice->id,
                'note' => $advice->note,
                'comment' => $advice->comment,
                'created_at' => $advice->created_at,
                'updated_at' => $advice->updated_at,
                'user' => $advice->user ? [
                    'id' => $advice->user->id,
                    'name' => $advice->user->name,
                    'avatar' => $advice->user->avatar ?? null,
                ] : null,
            ];
        });

        // Organiser les attributs disponibles (toujours retourner les attributs)
        $data['available_attributes'] = $produit->attributes->map(function ($attribute) {
            return [
                'id' => $attribute->id,
                'name' => $attribute->name,
                'slug' => $attribute->slug,
                'type' => $attribute->type,
                'description' => $attribute->description,
                'is_required' => $attribute->pivot->is_required ?? false,
                'sort_order' => $attribute->pivot->sort_order ?? 0,
                'created_at' => $attribute->created_at,
                'updated_at' => $attribute->updated_at,
                'values' => $attribute->activeValues->map(function ($value) {
                    return [
                        'id' => $value->id,
                        'value' => $value->value,
                        'label' => $value->label,
                        'color_code' => $value->color_code,
                        'price_modifier' => $value->price_modifier,
                        'sort_order' => $value->sort_order,
                        'is_active' => $value->is_active,
                        'created_at' => $value->created_at,
                        'updated_at' => $value->updated_at,
                    ];
                }),
            ];
        });

        // Si le produit a des variantes, ajouter des informations détaillées
        if ($hasVariants) {
            $data['variants'] = $produit->variants()->with([
                'attributeValues.productAttribute'
            ])->get()->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'name' => $variant->name,
                    'full_name' => $variant->getFullNameAttribute(),
                    'price' => $variant->price,
                    'formatted_price' => $variant->getFormattedPriceAttribute(),
                    'compare_price' => $variant->compare_price,
                    'cost_price' => $variant->cost_price,
                    'stock_quantity' => $variant->stock_quantity,
                    'weight' => $variant->weight,
                    'barcode' => $variant->barcode,
                    'image' => $variant->image,
                    'is_active' => $variant->is_active,
                    'track_inventory' => $variant->track_inventory,
                    'allow_backorder' => $variant->allow_backorder,
                    'is_in_stock' => $variant->isInStock(),
                    'can_be_ordered' => $variant->canBeOrdered(),
                    'created_at' => $variant->created_at,
                    'updated_at' => $variant->updated_at,
                    'attributes' => $variant->attributeValues->map(function ($attributeValue) {
                        return [
                            'attribute_id' => $attributeValue->productAttribute->id,
                            'attribute_name' => $attributeValue->productAttribute->name,
                            'attribute_slug' => $attributeValue->productAttribute->slug,
                            'attribute_type' => $attributeValue->productAttribute->type,
                            'attribute_description' => $attributeValue->productAttribute->description,
                            'value_id' => $attributeValue->id,
                            'value' => $attributeValue->value,
                            'label' => $attributeValue->label,
                            'color_code' => $attributeValue->color_code,
                            'price_modifier' => $attributeValue->price_modifier,
                            'sort_order' => $attributeValue->sort_order,
                            'is_active' => $attributeValue->is_active,
                        ];
                    }),
                ];
            });
        } else {
            // Pour les produits sans variantes, initialiser un tableau vide
            $data['variants'] = [];
        }

        // Statistiques supplémentaires
        $data['statistics'] = [
            'total_reviews' => $produit->advices->count(),
            'average_rating' => $produit->getAverageRatingAttribute(),
            'total_attributes' => $produit->attributes->count(),
            'total_variants' => $variantsCount,
            'active_variants' => $activeVariantsCount,
            'available_variants' => $availableVariantsCount,
            'total_stock' => $totalStock,
            'effective_stock' => $produit->getEffectiveStockAttribute(),
            'price_range' => $priceRange,
        ];

        return response()->json($data, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produit $produit)
    {
        // Décoder les chaînes JSON si nécessaire
        $attributes = $request->attributes;
        if (is_string($attributes)) {
            $decoded = json_decode($attributes, true);
            $attributes = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
        } elseif (!is_array($attributes)) {
            $attributes = [];
        }
        
        $variants = $request->variants;
        if (is_string($variants)) {
            $decoded = json_decode($variants, true);
            $variants = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
        } elseif (!is_array($variants)) {
            $variants = [];
        }

        // Déterminer si c'est un produit simple ou avec variantes
        $hasVariants = !empty($variants) && is_array($variants) && count($variants) > 0;

        $rules = [
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:25600',
            'description' => 'required|string',
            'product_type_id' => 'required|exists:product_types,id',
            'is_active' => 'nullable|in:true,false,1,0',
        ];

        // Règles conditionnelles selon le type de produit
        if ($hasVariants) {
            // Produit avec variantes : poids et stock gérés par les variantes
            $rules['poid'] = 'nullable|numeric|min:0';
            $rules['price'] = 'required|numeric|min:0'; // Prix de base
            $rules['stock_quantity'] = 'nullable|integer|min:0'; // Sera ignoré, calculé depuis les variantes
            
            // Règles pour les variantes
            $rules['variants'] = 'required|array|min:1';
            $rules['variants.*.sku'] = 'required|string|max:255';
            $rules['variants.*.name'] = 'nullable|string|max:255';
            $rules['variants.*.price'] = 'required|numeric|min:0';
            $rules['variants.*.stock_quantity'] = 'required|integer|min:0';
            $rules['variants.*.barcode'] = 'nullable|string|max:255';
            $rules['variants.*.is_active'] = 'nullable|boolean';
            $rules['variants.*.attributes'] = 'required|array';
            $rules['variants.*.attributes.*'] = 'exists:product_attribute_values,id';
            
            // Règles pour les attributs
            $rules['attributes'] = 'nullable|array';
            $rules['attributes.*'] = 'exists:product_attributes,id';
        } else {
            // Produit simple : poids et stock requis
            $rules['poid'] = 'required|numeric|min:0';
            $rules['price'] = 'required|numeric|min:0';
            $rules['stock_quantity'] = 'required|integer|min:0';
        }

        // Créer un tableau de données modifié pour la validation
        $validationData = $request->all();
        $validationData['attributes'] = $attributes;
        $validationData['variants'] = $variants;
        
        // Validation avec les données modifiées
        $validator = \Illuminate\Support\Facades\Validator::make($validationData, $rules);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreurs de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Convertir is_active en booléen
            $isActive = $request->is_active;
            if ($isActive === 'true' || $isActive === '1') {
                $isActive = true;
            } elseif ($isActive === 'false' || $isActive === '0') {
                $isActive = false;
            } else {
                $isActive = true;
            }

            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'poid' => $request->poid ?? 0,
                'price' => $request->price,
                'product_type_id' => $request->product_type_id,
                'is_active' => $isActive,
            ];

            // Gestion du stock selon le type de produit
            if ($hasVariants) {
                // Pour les produits avec variantes, calculer le stock total
                $totalStock = 0;
                foreach ($variants as $variantData) {
                    $totalStock += (int)$variantData['stock_quantity'];
                }
                $data['stock_quantity'] = $totalStock;
            } else {
                // Pour les produits simples, utiliser le stock fourni
                $data['stock_quantity'] = $request->stock_quantity;
            }

            // Gestion de l'image
            if ($request->hasFile('image')) {
                $filename = $request->image->getClientOriginalName();
                $request->image->storeAs('produits', $filename, 'public');
                $data['image'] = $filename;
            }

            // Mettre à jour le produit
            $produit->update($data);

            // Gestion des attributs (seulement si le produit a des variantes)
            if ($hasVariants && !empty($attributes) && is_array($attributes)) {
                // Supprimer les anciens attributs
                $produit->attributes()->detach();
                
                // Ajouter les nouveaux attributs
                $attributesData = [];
                foreach ($attributes as $index => $attributeId) {
                    $attributesData[$attributeId] = [
                        'is_required' => true,
                        'sort_order' => $index,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                $produit->attributes()->attach($attributesData);
            }

            // Gestion des variantes
            if ($hasVariants) {
                // Supprimer toutes les variantes existantes
                $produit->variants()->delete();
                
                // Créer les nouvelles variantes
                foreach ($variants as $index => $variantData) {
                    // Créer la variante
                    $variant = $produit->variants()->create([
                        'sku' => $variantData['sku'],
                        'name' => $variantData['name'] ?? null,
                        'price' => $variantData['price'],
                        'stock_quantity' => $variantData['stock_quantity'],
                        'barcode' => $variantData['barcode'] ?? null,
                        'is_active' => $variantData['is_active'] ?? true,
                        'track_inventory' => true,
                    ]);

                    // Gestion de l'image de la variante
                    $imageFound = false;
                    
                    // Vérifier avec underscore (format réel envoyé par le frontend)
                    if ($request->hasFile("variant_images_{$index}")) {
                        $variantImage = $request->file("variant_images_{$index}");
                        $originalName = $variantImage->getClientOriginalName();
                        $extension = $variantImage->getClientOriginalExtension();
                        $fileName = pathinfo($originalName, PATHINFO_FILENAME);
                        
                        // Créer un nom de fichier unique pour éviter les conflits
                        $variantImageName = $fileName . '_' . time() . '_' . uniqid() . '.' . $extension;
                        
                        // Stocker l'image dans le dossier variants
                        $variantImage->storeAs('variants', $variantImageName, 'public');
                        
                        // Mettre à jour la variante avec le nom de l'image
                        $variant->update(['image' => $variantImageName]);
                        $imageFound = true;
                    }
                    // Fallback: vérifier aussi avec le point (au cas où)
                    elseif ($request->hasFile("variant_images.{$index}")) {
                        $variantImage = $request->file("variant_images.{$index}");
                        $originalName = $variantImage->getClientOriginalName();
                        $extension = $variantImage->getClientOriginalExtension();
                        $fileName = pathinfo($originalName, PATHINFO_FILENAME);
                        
                        // Créer un nom de fichier unique pour éviter les conflits
                        $variantImageName = $fileName . '_' . time() . '_' . uniqid() . '.' . $extension;
                        
                        // Stocker l'image dans le dossier variants
                        $variantImage->storeAs('variants', $variantImageName, 'public');
                        
                        // Mettre à jour la variante avec le nom de l'image
                        $variant->update(['image' => $variantImageName]);
                        $imageFound = true;
                    }
                    
                    if (!$imageFound) {
                        // \Log::info("Aucune image trouvée pour la variante {$index}"); // Removed debug log
                    }

                    // Associer les valeurs d'attributs à la variante
                    if (!empty($variantData['attributes'])) {
                        $attributeValues = [];
                        foreach ($variantData['attributes'] as $attributeId => $valueId) {
                            $attributeValues[] = $valueId;
                        }
                        $variant->attributeValues()->attach($attributeValues);
                    }
                }
            }

            DB::commit();

            $responseMessage = $hasVariants 
                ? "Le produit avec variantes a bien été mis à jour. Stock total : {$data['stock_quantity']} unités." 
                : 'Le produit simple a bien été mis à jour.';

            return response()->json([
                'message' => $responseMessage,
                'produit' => $produit->load(['productType', 'variants.attributeValues.productAttribute', 'attributes']),
                'has_variants' => $hasVariants,
                'variants_count' => $hasVariants ? count($variants) : 0,
                'total_stock' => $data['stock_quantity']
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'error' => 'Erreur lors de la mise à jour du produit',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produit $produit)
    {
        $produit->delete();
        return response()->json([
            'message' => "Le produit a bien été supprimé."
        ], 200);
    }

    /**
     * Toggle product status (active/inactive)
     */
    public function toggleStatus(Produit $produit)
    {
        $produit->update([
            'is_active' => !$produit->is_active
        ]);

        $status = $produit->is_active ? 'activé' : 'désactivé';

        return response()->json([
            'message' => "Le produit a été {$status} avec succès.",
            'produit' => $produit->load('productType')
        ], 200);
    }
}
