<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('telephone')->nullable();
            $table->string('cin')->unique()->nullable();
            $table->double('taille')->nullable(); // en mètres
            $table->json('poids')->nullable(); // Historique de poids : [{"date": "...", "valeur": 72.5}]
            $table->enum('objectif', [
                'prise de masse',
                'perte de poids',
                'maintien',
                'prise de force',
                'endurance',
                'remise en forme',
                'sèche',
                'souplesse',
                'rééducation',
                'tonification',
                'préparation physique',
                'performance',
            ])->nullable();
            $table->enum('sexe', ['homme', 'femme'])->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('avatar')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
