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
        'user_id'
    ];

    //retourne l'user qui a publié le produit $produit->user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //retourne tout les avis du produit $produit->advices
    public function advices()
    {
        return $this->hasMany(Advice::class);
    }

    //retourne la note du produit $produit->getAverageRatingAttribute
    public function getAverageRatingAttribute()
    {
        return $this->advices()->avg('note');
    }
}
