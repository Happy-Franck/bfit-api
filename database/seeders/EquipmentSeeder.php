<?php

namespace Database\Seeders;

use App\Models\Equipment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $equipments = [
            [
                'name' => 'Haltère',
                'description' => 'Poids libre avec barre courte pour exercices unilatéraux',
                'image' => null,
                'user_id' => null,
            ],
            [
                'name' => 'Haltères',
                'description' => 'Paire d\'haltères pour exercices bilatéraux',
                'image' => null,
                'user_id' => null,
            ],
            [
                'name' => 'Poids corporel',
                'description' => 'Exercices utilisant uniquement le poids du corps',
                'image' => null,
                'user_id' => null,
            ],
            [
                'name' => 'Machine',
                'description' => 'Machines de musculation guidées',
                'image' => null,
                'user_id' => null,
            ],
            [
                'name' => 'Médecine-ball',
                'description' => 'Ballon lesté pour exercices fonctionnels',
                'image' => null,
                'user_id' => null,
            ],
            [
                'name' => 'Kettlebells',
                'description' => 'Poids en forme de boulet avec poignée',
                'image' => null,
                'user_id' => null,
            ],
            [
                'name' => 'Étirements',
                'description' => 'Exercices de flexibilité et mobilité',
                'image' => null,
                'user_id' => null,
            ],
            [
                'name' => 'Câbles',
                'description' => 'Système de poulies et câbles',
                'image' => null,
                'user_id' => null,
            ],
            [
                'name' => 'Groupe',
                'description' => 'Exercices en groupe ou partenariat',
                'image' => null,
                'user_id' => null,
            ],
            [
                'name' => 'Plaque',
                'description' => 'Disques de fonte pour barres',
                'image' => null,
                'user_id' => null,
            ],
            [
                'name' => 'TRX',
                'description' => 'Sangles de suspension pour entraînement fonctionnel',
                'image' => null,
                'user_id' => null,
            ],
            [
                'name' => 'Yoga',
                'description' => 'Pratique du yoga et exercices de bien-être',
                'image' => null,
                'user_id' => null,
            ],
            [
                'name' => 'Ballon Bosu',
                'description' => 'Demi-ballon pour exercices d\'équilibre',
                'image' => null,
                'user_id' => null,
            ],
            [
                'name' => 'Vitruve',
                'description' => 'Capteur de vitesse pour entraînement basé sur la vitesse',
                'image' => null,
                'user_id' => null,
            ],
            [
                'name' => 'Cardio',
                'description' => 'Appareils cardiovasculaires (tapis, vélo, etc.)',
                'image' => null,
                'user_id' => null,
            ],
            [
                'name' => 'Smith Machine',
                'description' => 'Machine guidée avec barre sur rails',
                'image' => null,
                'user_id' => null,
            ],
            [
                'name' => 'Récupération',
                'description' => 'Outils et techniques de récupération',
                'image' => null,
                'user_id' => null,
            ],
        ];

        foreach ($equipments as $equipment) {
            Equipment::create($equipment);
        }
    }
} 