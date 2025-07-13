<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'price',
        'compare_price',
        'cost_price',
        'stock_quantity',
        'weight',
        'barcode',
        'image',
        'track_inventory',
        'allow_backorder',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'weight' => 'integer',
        'track_inventory' => 'boolean',
        'allow_backorder' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relation avec le produit parent
    public function product()
    {
        return $this->belongsTo(Produit::class, 'product_id');
    }

    // Relation many-to-many avec les valeurs d'attributs
    public function attributeValues()
    {
        return $this->belongsToMany(ProductAttributeValue::class, 'product_variant_attributes')
            ->withTimestamps();
    }

    // Obtenir les attributs de cette variante avec leurs valeurs
    public function getAttributesWithValues()
    {
        return $this->attributeValues()
            ->with('productAttribute')
            ->get()
            ->groupBy('productAttribute.name');
    }

    // Scope pour récupérer seulement les variantes actives
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope pour récupérer les variantes en stock
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    // Vérifier si la variante est en stock
    public function isInStock($quantity = 1)
    {
        return $this->stock_quantity >= $quantity;
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
        return number_format($this->price, 2) . ' Ar';
    }

    // Obtenir le nom complet de la variante
    public function getFullNameAttribute()
    {
        if ($this->name) {
            return $this->name;
        }
        
        $attributeNames = $this->attributeValues()
            ->with('productAttribute')
            ->get()
            ->map(function ($attributeValue) {
                return $attributeValue->label ?: $attributeValue->value;
            })
            ->join(' - ');
            
        return $this->product->name . ($attributeNames ? ' - ' . $attributeNames : '');
    }

    // Vérifier si la variante peut être commandée
    public function canBeOrdered($quantity = 1)
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->track_inventory) {
            return $this->isInStock($quantity) || $this->allow_backorder;
        }

        return true;
    }
} 