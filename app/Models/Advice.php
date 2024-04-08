<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advice extends Model
{
    use HasFactory;

    protected $fillable = [
        'comment',
        'note',
        'user_id',
        'produit_id',
    ];

    //user qui a ecris le commentaire $advice->user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //le produit dont le commentaire appartient $advice->produit
    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }
}
