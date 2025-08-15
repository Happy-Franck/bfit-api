<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Training extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'image_homme',
        'image_femme',
        'video',
        'user_id',
        'equipment_id',
    ];

    protected $appends = [
        'image_url',
        'image_homme_url',
        'image_femme_url',
        'video_url',
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
     * Relation belongsTo vers l'équipement principal (optionnel)
     */
    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
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

    private function makeAbsoluteUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }
        $normalized = ltrim($path, '/');
        return asset($normalized);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->makeAbsoluteUrl($this->image);
    }

    public function getImageHommeUrlAttribute(): ?string
    {
        return $this->makeAbsoluteUrl($this->image_homme);
    }

    public function getImageFemmeUrlAttribute(): ?string
    {
        return $this->makeAbsoluteUrl($this->image_femme);
    }

    public function getVideoUrlAttribute(): ?string
    {
        return $this->makeAbsoluteUrl($this->video);
    }
}
