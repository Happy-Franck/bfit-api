<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'video',
        'user_id',
    ];

    /**
     * Relation avec l'utilisateur qui a créé l'exercice
     */
    public function user(){
        return $this->belongsTo(User::class);
    }

    /**
     * Relation many-to-many avec les catégories
     * Un exercice peut appartenir à plusieurs catégories musculaires
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Relation many-to-many avec les équipements
     * Un exercice peut nécessiter plusieurs équipements
     */
    public function equipments()
    {
        return $this->belongsToMany(Equipment::class);
    }

    /**
     * Relation many-to-many avec les séances
     */
    public function seances()
    {
        return $this->belongsToMany(Seance::class)
            ->withPivot('id', 'series', 'repetitions', 'duree')
            ->withTimestamps();
    }
}
