<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'description',
        'poid',
        'price',
        'user_id',
        'product_type_id',
        'stock_quantity',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean'
    ];

    //retourne l'user qui a publié le produit $produit->user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //retourne le type du produit $produit->productType
    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }

    //retourne tout les avis du produit $produit->advices
    public function advices()
    {
        return $this->hasMany(Advice::class);
    }

    // Relations pour le système de variantes
    
    // Relation avec les variantes du produit
    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    // Relation avec les variantes actives
    public function activeVariants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id')->where('is_active', true);
    }

    // Relation avec les variantes en stock
    public function availableVariants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id')
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0);
    }

    // Relation many-to-many avec les attributs de produit
    public function attributes()
    {
        return $this->belongsToMany(ProductAttribute::class, 'product_product_attributes', 'product_id', 'product_attribute_id')
            ->withPivot('is_required', 'sort_order')
            ->withTimestamps()
            ->orderBy('product_product_attributes.sort_order');
    }

    // Relation avec les attributs actifs
    public function activeAttributes()
    {
        return $this->belongsToMany(ProductAttribute::class, 'product_product_attributes', 'product_id', 'product_attribute_id')
            ->where('product_attributes.is_active', true)
            ->withPivot('is_required', 'sort_order')
            ->withTimestamps()
            ->orderBy('product_product_attributes.sort_order');
    }

    // Obtenir toutes les valeurs d'attributs utilisées par ce produit
    public function getAttributeValues()
    {
        $attributeIds = $this->attributes()->pluck('product_attributes.id');
        return ProductAttributeValue::whereIn('product_attribute_id', $attributeIds)
            ->with('productAttribute')
            ->get()
            ->groupBy('productAttribute.name');
    }

    //retourne la note du produit $produit->getAverageRatingAttribute
    public function getAverageRatingAttribute()
    {
        return $this->advices()->avg('note');
    }

    // Scope pour récupérer seulement les produits actifs
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope pour récupérer les produits en stock
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    // Scope pour récupérer les produits par type
    public function scopeOfType($query, $typeId)
    {
        return $query->where('product_type_id', $typeId);
    }

    // Obtenir le stock total (produit + variantes)
    public function getTotalStockAttribute()
    {
        if ($this->hasVariants()) {
            // Si le produit a des variantes, le stock total = somme des stocks des variantes
            return $this->activeVariants()->sum('stock_quantity');
        } else {
            // Si le produit n'a pas de variantes, utiliser le stock du produit
            return $this->stock_quantity;
        }
    }

    // Vérifier si le produit est en stock (gestion intelligente)
    public function isInStock($quantity = 1)
    {
        if ($this->hasVariants()) {
            // Si le produit a des variantes, vérifier le stock total des variantes
            return $this->getTotalStockAttribute() >= $quantity;
        } else {
            // Si le produit n'a pas de variantes, vérifier le stock du produit
            return $this->stock_quantity >= $quantity;
        }
    }

    // Obtenir le stock effectif pour l'affichage
    public function getEffectiveStockAttribute()
    {
        if ($this->hasVariants()) {
            return $this->getTotalStockAttribute();
        } else {
            return $this->stock_quantity;
        }
    }

    // Vérifier si le produit peut être commandé
    public function canBeOrdered($quantity = 1)
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->hasVariants()) {
            // Si le produit a des variantes, vérifier qu'au moins une variante est en stock
            return $this->availableVariants()->exists();
        } else {
            return $this->isInStock($quantity);
        }
    }

    // Réduire le stock
    public function reduceStock($quantity)
    {
        if ($this->isInStock($quantity)) {
            $this->decrement('stock_quantity', $quantity);
            return true;
        }
        return false;
    }

    // Augmenter le stock
    public function increaseStock($quantity)
    {
        $this->increment('stock_quantity', $quantity);
    }

    // Obtenir le prix formaté
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2) . ' FCFA';
    }

    // Obtenir la gamme de prix (si variantes)
    public function getPriceRangeAttribute()
    {
        $variants = $this->activeVariants;
        
        if ($variants->isEmpty()) {
            return ['min' => $this->price, 'max' => $this->price];
        }
        
        $prices = $variants->pluck('price')->push($this->price);
        
        return [
            'min' => $prices->min(),
            'max' => $prices->max()
        ];
    }

    // Vérifier si le produit a des variantes
    public function hasVariants()
    {
        return $this->variants()->exists();
    }

    // Obtenir la variante par défaut (première variante active)
    public function getDefaultVariant()
    {
        return $this->activeVariants()->first();
    }
}
