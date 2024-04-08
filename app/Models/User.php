<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    //tout les produits que l'user a publié $user->produits
    public function produits(){
        return $this->hasMany(Produit::class);
    }

    //tout les avis que l'utilisateur a publié $user->advices
    public function advices()
    {
        return $this->hasMany(Advice::class);
    }

    //many to many entre coach et challengers
    //récupérer les challengers coachés
    //$coach = User::find(1);
    //$challengers = $coach->coachedChallengers;
    public function challengers()
    {
        //1er arg -> relation avec model user
        //2e arg -> nom de table de liaison
        //3e arg -> coach a plusieur
        //4e arg -> challenger
        return $this->belongsToMany(User::class, 'coaching', 'coach_id', 'challenger_id');
    }

    //many to many entre challenger et coachs
    //récupérer les coaches d'un challenger
    //$challenger = User::find(2);
    //$coaches = $challenger->coaches;
    public function coachs()
    {
        //1er arg -> relation avec model user
        //2e arg -> nom de table de liaison
        //3e arg -> challenger a plusieurs
        //4e arg -> coach
        return $this->belongsToMany(User::class, 'coaching', 'challenger_id', 'coach_id');
    }

    public function categories(){
        return $this->hasMany(Category::class);
    }

    public function trainings()
    {
        return $this->hasMany(Training::class);
    }

    /* tout les sceance créé par l'admin */
    public function seances(){
        return $this->hasMany(Seance::class, 'admin_id');
    }

    /* tout les scéance donné par le coach */
    public function coachSeances()
    {
        return $this->hasMany(Seance::class, 'coach_id');
    }

    /* tout les scéance fait par le challenger */
    public function challengerSeances()
    {
        return $this->hasMany(Seance::class, 'challenger_id');
    }
}
