<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'user_id',
    ];

    /**
     * Relation avec l'utilisateur qui a créé l'équipement
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation many-to-many avec les trainings
     * Un équipement peut être utilisé par plusieurs exercices
     */
    public function trainings()
    {
        return $this->belongsToMany(Training::class);
    }
} 