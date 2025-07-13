<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_attribute_id',
        'value',
        'label',
        'color_code',
        'price_modifier',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    // Relation avec l'attribut parent
    public function productAttribute()
    {
        return $this->belongsTo(ProductAttribute::class);
    }

    // Relation many-to-many avec les variantes de produits
    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_attributes')
            ->withTimestamps();
    }

    // Scope pour récupérer seulement les valeurs actives
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope pour récupérer les valeurs par attribut
    public function scopeForAttribute($query, $attributeId)
    {
        return $query->where('product_attribute_id', $attributeId);
    }

    // Scope pour récupérer les valeurs de couleur
    public function scopeColors($query)
    {
        return $query->whereHas('productAttribute', function ($q) {
            $q->where('type', 'color');
        });
    }

    // Obtenir le nombre de variantes utilisant cette valeur
    public function getVariantsCountAttribute()
    {
        return $this->productVariants()->count();
    }

    // Vérifier si la valeur est utilisée par des variantes
    public function isUsedByVariants()
    {
        return $this->productVariants()->exists();
    }

    // Obtenir le prix avec modificateur appliqué
    public function getPriceWithModifier($basePrice)
    {
        return $basePrice + ($this->price_modifier ?? 0);
    }

    // Obtenir le label ou la valeur par défaut
    public function getDisplayNameAttribute()
    {
        return $this->label ?: $this->value;
    }

    // Obtenir la couleur formatée pour l'affichage
    public function getFormattedColorAttribute()
    {
        if ($this->productAttribute->type === 'color' && $this->color_code) {
            return [
                'name' => $this->getDisplayNameAttribute(),
                'code' => $this->color_code,
                'style' => "background-color: {$this->color_code}"
            ];
        }
        return null;
    }

    // Obtenir toutes les variantes actives utilisant cette valeur
    public function getActiveVariants()
    {
        return $this->productVariants()
            ->where('is_active', true)
            ->with('product')
            ->get();
    }
} 