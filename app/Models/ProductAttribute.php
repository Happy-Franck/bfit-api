<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'sort_order',
        'is_required',
        'is_active'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relation avec les valeurs d'attributs
    public function values()
    {
        return $this->hasMany(ProductAttributeValue::class)->orderBy('sort_order');
    }

    // Relation avec les valeurs d'attributs actives
    public function activeValues()
    {
        return $this->hasMany(ProductAttributeValue::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    // Relation many-to-many avec les produits
    public function products()
    {
        return $this->belongsToMany(Produit::class, 'product_product_attributes')
            ->withPivot('is_required', 'sort_order')
            ->withTimestamps();
    }

    // Scope pour récupérer seulement les attributs actifs
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope pour récupérer les attributs par type
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Scope pour récupérer les attributs obligatoires
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    // Obtenir le nombre de produits utilisant cet attribut
    public function getProductsCountAttribute()
    {
        return $this->products()->count();
    }

    // Obtenir le nombre de valeurs actives
    public function getActiveValuesCountAttribute()
    {
        return $this->activeValues()->count();
    }

    // Vérifier si l'attribut est utilisé par des produits
    public function isUsedByProducts()
    {
        return $this->products()->exists();
    }

    // Obtenir toutes les variantes utilisant cet attribut
    public function getVariantsUsingAttribute()
    {
        $valueIds = $this->values()->pluck('id');
        return ProductVariant::whereHas('attributeValues', function ($query) use ($valueIds) {
            $query->whereIn('product_attribute_value_id', $valueIds);
        })->get();
    }
} 