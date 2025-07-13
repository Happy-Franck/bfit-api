<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Génération automatique du slug
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($productType) {
            if (empty($productType->slug)) {
                $productType->slug = Str::slug($productType->name);
            }
        });
    }

    // Retourne tous les produits de ce type
    public function produits()
    {
        return $this->hasMany(Produit::class);
    }

    // Retourne seulement les produits actifs de ce type
    public function activeProduits()
    {
        return $this->hasMany(Produit::class)->where('is_active', true);
    }

    // Relation many-to-many avec les attributs de produit
    public function attributes()
    {
        return $this->belongsToMany(ProductAttribute::class, 'product_type_attributes', 'product_type_id', 'product_attribute_id')
            ->withPivot('is_required', 'sort_order')
            ->withTimestamps()
            ->orderBy('product_type_attributes.sort_order');
    }

    // Relation avec les attributs actifs
    public function activeAttributes()
    {
        return $this->belongsToMany(ProductAttribute::class, 'product_type_attributes', 'product_type_id', 'product_attribute_id')
            ->where('product_attributes.is_active', true)
            ->withPivot('is_required', 'sort_order')
            ->withTimestamps()
            ->orderBy('product_type_attributes.sort_order');
    }

    // Scope pour récupérer seulement les types actifs
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
} 