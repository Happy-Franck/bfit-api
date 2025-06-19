<?php

namespace Database\Seeders;

use App\Models\Training;
use App\Models\Category;
use App\Models\Equipment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TrainingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les catégories pour les relations
        $pectoraux = Category::where('name', 'Pectoraux')->first();
        $abdominaux = Category::where('name', 'Abdominaux')->first();
        $obliques = Category::where('name', 'Obliques')->first();
        $grandsDoesaux = Category::where('name', 'Grands dorsaux')->first();
        $rhomboides = Category::where('name', 'Rhomboïdes')->first();
        $trapezes = Category::where('name', 'Trapèzes')->first();
        $deltoides = Category::where('name', 'Déltoïdes')->first();
        $biceps = Category::where('name', 'Biceps')->first();
        $triceps = Category::where('name', 'Triceps')->first();
        $brachiaux = Category::where('name', 'Brachiaux')->first();
        $avantBras = Category::where('name', 'Avant-bras')->first();
        $fessier = Category::where('name', 'Fessier')->first();
        $quadriceps = Category::where('name', 'Quadriceps')->first();
        $ishio = Category::where('name', 'Ishio')->first();
        $mollets = Category::where('name', 'Mollets')->first();

        // Récupérer les équipements pour les relations
        $poidsCorporel = Equipment::where('name', 'Poids corporel')->first();
        $halteres = Equipment::where('name', 'Haltères')->first();
        $haltere = Equipment::where('name', 'Haltère')->first();
        $machine = Equipment::where('name', 'Machine')->first();
        $kettlebells = Equipment::where('name', 'Kettlebells')->first();
        $cables = Equipment::where('name', 'Câbles')->first();
        $medecineBlall = Equipment::where('name', 'Médecine-ball')->first();
        $plaque = Equipment::where('name', 'Plaque')->first();
        $smithMachine = Equipment::where('name', 'Smith Machine')->first();
        $ballonBosu = Equipment::where('name', 'Ballon Bosu')->first();

        // EXERCICES PECTORAUX
        $training1 = Training::create([
            'name' => 'Pompes classiques',
            'description' => 'Exercice de base au poids du corps pour développer les pectoraux, les triceps et les deltoïdes antérieurs. Position de planche, descente contrôlée jusqu\'à effleurer le sol puis remontée explosive.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training1->categories()->attach([$pectoraux->id, $triceps->id, $deltoides->id]);
        $training1->equipments()->attach([$poidsCorporel->id]);

        $training2 = Training::create([
            'name' => 'Développé couché barre',
            'description' => 'Exercice roi pour les pectoraux. Allongé sur banc, saisir la barre en prise large, descendre lentement vers la poitrine puis pousser vers le haut en contractant les pectoraux.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training2->categories()->attach([$pectoraux->id, $triceps->id, $deltoides->id]);
        $training2->equipments()->attach([$plaque->id]);

        $training3 = Training::create([
            'name' => 'Développé incliné haltères',
            'description' => 'Développé sur banc incliné à 30-45° pour cibler le haut des pectoraux. Mouvement contrôlé avec haltères pour une amplitude maximale.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training3->categories()->attach([$pectoraux->id, $deltoides->id]);
        $training3->equipments()->attach([$halteres->id]);

        $training4 = Training::create([
            'name' => 'Écarté couché haltères',
            'description' => 'Isolation des pectoraux. Allongé, bras écartés avec haltères, descendre en arc de cercle puis remonter en contractant les pectoraux.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training4->categories()->attach([$pectoraux->id]);
        $training4->equipments()->attach([$halteres->id]);

        // EXERCICES ABDOMINAUX
        $training5 = Training::create([
            'name' => 'Crunch classique',
            'description' => 'Exercice de base pour les abdominaux. Allongé, genoux fléchis, mains derrière la tête. Décoller les épaules du sol en contractant les abdos.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training5->categories()->attach([$abdominaux->id]);
        $training5->equipments()->attach([$poidsCorporel->id]);

        $training6 = Training::create([
            'name' => 'Planche',
            'description' => 'Gainage statique complet. Position de pompe sur les avant-bras, corps droit et rigide. Maintenir la position en contractant tous les muscles du tronc.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training6->categories()->attach([$abdominaux->id, $obliques->id]);
        $training6->equipments()->attach([$poidsCorporel->id]);

        $training7 = Training::create([
            'name' => 'Relevé de genoux suspendu',
            'description' => 'Suspendu à une barre, relever les genoux vers la poitrine en contractant les abdominaux. Mouvement contrôlé sans balancement.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training7->categories()->attach([$abdominaux->id]);
        $training7->equipments()->attach([$poidsCorporel->id]);

        // EXERCICES OBLIQUES
        $training8 = Training::create([
            'name' => 'Russian Twist',
            'description' => 'Assis, pieds décollés, rotation du buste de gauche à droite en tenant un poids. Excellent pour les obliques et la stabilité du tronc.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training8->categories()->attach([$obliques->id, $abdominaux->id]);
        $training8->equipments()->attach([$medecineBlall->id]);

        $training9 = Training::create([
            'name' => 'Planche latérale',
            'description' => 'Gainage latéral pour cibler spécifiquement les obliques. Sur le côté, appui sur un avant-bras, corps aligné.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training9->categories()->attach([$obliques->id]);
        $training9->equipments()->attach([$poidsCorporel->id]);

        // EXERCICES GRANDS DORSAUX
        $training10 = Training::create([
            'name' => 'Tractions',
            'description' => 'Exercice roi pour le dos. Suspendu à une barre, tirer le corps vers le haut jusqu\'à amener le menton au-dessus de la barre.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training10->categories()->attach([$grandsDoesaux->id, $biceps->id, $rhomboides->id]);
        $training10->equipments()->attach([$poidsCorporel->id]);

        $training11 = Training::create([
            'name' => 'Tirage horizontal',
            'description' => 'Assis, tirer la barre vers l\'abdomen en serrant les omoplates. Excellent pour l\'épaisseur du dos.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training11->categories()->attach([$grandsDoesaux->id, $rhomboides->id, $biceps->id]);
        $training11->equipments()->attach([$cables->id]);

        $training12 = Training::create([
            'name' => 'Tirage vertical prise large',
            'description' => 'Tirage vers le haut de la poitrine avec prise large pour développer la largeur des dorsaux.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training12->categories()->attach([$grandsDoesaux->id, $rhomboides->id]);
        $training12->equipments()->attach([$cables->id]);

        // EXERCICES TRAPÈZES
        $training13 = Training::create([
            'name' => 'Shrugs',
            'description' => 'Haussements d\'épaules avec haltères ou barre pour développer les trapèzes supérieurs. Mouvement vertical contrôlé.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training13->categories()->attach([$trapezes->id]);
        $training13->equipments()->attach([$halteres->id]);

        $training14 = Training::create([
            'name' => 'Rowing menton',
            'description' => 'Tirer la barre vers le menton, coudes hauts, pour travailler les trapèzes moyens et les deltoïdes.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training14->categories()->attach([$trapezes->id, $deltoides->id]);
        $training14->equipments()->attach([$plaque->id]);

        // EXERCICES DELTOÏDES
        $training15 = Training::create([
            'name' => 'Développé militaire',
            'description' => 'Développé debout avec barre, excellent pour les deltoïdes antérieurs et la stabilité du tronc.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training15->categories()->attach([$deltoides->id, $triceps->id]);
        $training15->equipments()->attach([$plaque->id]);

        $training16 = Training::create([
            'name' => 'Élévations latérales',
            'description' => 'Élever les haltères sur les côtés jusqu\'à l\'horizontale pour isoler les deltoïdes moyens.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training16->categories()->attach([$deltoides->id]);
        $training16->equipments()->attach([$halteres->id]);

        $training17 = Training::create([
            'name' => 'Oiseau',
            'description' => 'Penché en avant, élever les haltères sur les côtés pour travailler les deltoïdes postérieurs.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training17->categories()->attach([$deltoides->id, $rhomboides->id]);
        $training17->equipments()->attach([$halteres->id]);

        // EXERCICES BICEPS
        $training18 = Training::create([
            'name' => 'Curl barre droite',
            'description' => 'Flexion des avant-bras avec barre, exercice de base pour les biceps. Mouvement contrôlé sans balancement.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training18->categories()->attach([$biceps->id]);
        $training18->equipments()->attach([$plaque->id]);

        $training19 = Training::create([
            'name' => 'Curl haltères alterné',
            'description' => 'Curl avec haltères en alternant les bras pour une meilleure concentration et amplitude.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training19->categories()->attach([$biceps->id]);
        $training19->equipments()->attach([$halteres->id]);

        $training20 = Training::create([
            'name' => 'Curl marteau',
            'description' => 'Curl with prise neutre (marteau) pour cibler les biceps et les brachiaux.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training20->categories()->attach([$biceps->id, $brachiaux->id, $avantBras->id]);
        $training20->equipments()->attach([$halteres->id]);

        // EXERCICES TRICEPS
        $training21 = Training::create([
            'name' => 'Dips',
            'description' => 'Flexions aux barres parallèles pour développer les triceps et les pectoraux inférieurs.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training21->categories()->attach([$triceps->id, $pectoraux->id]);
        $training21->equipments()->attach([$poidsCorporel->id]);

        $training22 = Training::create([
            'name' => 'Extension triceps couché',
            'description' => 'Allongé, extension des avant-bras avec barre EZ pour isoler les triceps.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training22->categories()->attach([$triceps->id]);
        $training22->equipments()->attach([$plaque->id]);

        $training23 = Training::create([
            'name' => 'Extension triceps debout',
            'description' => 'Extension verticale avec haltère derrière la tête pour étirer et développer les triceps.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training23->categories()->attach([$triceps->id]);
        $training23->equipments()->attach([$haltere->id]);

        // EXERCICES AVANT-BRAS
        $training24 = Training::create([
            'name' => 'Curl poignets',
            'description' => 'Flexion des poignets avec barre pour développer les fléchisseurs des avant-bras.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training24->categories()->attach([$avantBras->id]);
        $training24->equipments()->attach([$plaque->id]);

        $training25 = Training::create([
            'name' => 'Farmer Walk',
            'description' => 'Marche avec charges lourdes dans chaque main pour développer la force de préhension et les avant-bras.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training25->categories()->attach([$avantBras->id, $trapezes->id]);
        $training25->equipments()->attach([$halteres->id]);

        // EXERCICES FESSIERS
        $training26 = Training::create([
            'name' => 'Squats',
            'description' => 'Exercice roi pour les jambes et fessiers. Flexion des genoux avec barre sur les épaules, descendre jusqu\'aux cuisses parallèles.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training26->categories()->attach([$fessier->id, $quadriceps->id]);
        $training26->equipments()->attach([$plaque->id]);

        $training27 = Training::create([
            'name' => 'Hip Thrust',
            'description' => 'Allongé, dos appuyé sur banc, pousser les hanches vers le haut avec barre pour cibler spécifiquement les fessiers.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training27->categories()->attach([$fessier->id]);
        $training27->equipments()->attach([$plaque->id]);

        $training28 = Training::create([
            'name' => 'Fentes',
            'description' => 'Pas en avant avec flexion des deux genoux pour travailler fessiers et quadriceps de manière unilatérale.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training28->categories()->attach([$fessier->id, $quadriceps->id]);
        $training28->equipments()->attach([$poidsCorporel->id]);

        // EXERCICES QUADRICEPS
        $training29 = Training::create([
            'name' => 'Leg Press',
            'description' => 'Poussée des jambes sur machine pour développer la force des quadriceps avec charge lourde.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training29->categories()->attach([$quadriceps->id, $fessier->id]);
        $training29->equipments()->attach([$machine->id]);

        $training30 = Training::create([
            'name' => 'Extension de jambes',
            'description' => 'Isolation des quadriceps sur machine, extension des jambes depuis position assise.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training30->categories()->attach([$quadriceps->id]);
        $training30->equipments()->attach([$machine->id]);

        $training31 = Training::create([
            'name' => 'Squat bulgare',
            'description' => 'Squat unilatéral avec pied arrière surélevé pour cibler intensément quadriceps et fessiers.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training31->categories()->attach([$quadriceps->id, $fessier->id]);
        $training31->equipments()->attach([$poidsCorporel->id]);

        // EXERCICES ISCHIO-JAMBIERS
        $training32 = Training::create([
            'name' => 'Soulevé de terre',
            'description' => 'Exercice complet, soulever la barre depuis le sol en gardant le dos droit. Excellent pour les ischio-jambiers et fessiers.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training32->categories()->attach([$ishio->id, $fessier->id, $grandsDoesaux->id, $trapezes->id]);
        $training32->equipments()->attach([$plaque->id]);

        $training33 = Training::create([
            'name' => 'Leg Curl',
            'description' => 'Flexion des jambes sur machine pour isoler les ischio-jambiers. Mouvement contrôlé avec contraction en fin de mouvement.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training33->categories()->attach([$ishio->id]);
        $training33->equipments()->attach([$machine->id]);

        $training34 = Training::create([
            'name' => 'Soulevé de terre roumain',
            'description' => 'Variante du soulevé de terre avec jambes tendues pour cibler spécifiquement les ischio-jambiers.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training34->categories()->attach([$ishio->id, $fessier->id]);
        $training34->equipments()->attach([$plaque->id]);

        // EXERCICES MOLLETS
        $training35 = Training::create([
            'name' => 'Mollets debout',
            'description' => 'Extension des chevilles en position debout avec charge pour développer les mollets (gastrocnémiens).',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training35->categories()->attach([$mollets->id]);
        $training35->equipments()->attach([$machine->id]);

        $training36 = Training::create([
            'name' => 'Mollets assis',
            'description' => 'Extension des chevilles en position assise pour cibler le muscle soléaire des mollets.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training36->categories()->attach([$mollets->id]);
        $training36->equipments()->attach([$machine->id]);

        $training37 = Training::create([
            'name' => 'Sauts sur box',
            'description' => 'Sauts explosifs sur une box pour développer la puissance et les mollets de manière fonctionnelle.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training37->categories()->attach([$mollets->id, $quadriceps->id, $fessier->id]);
        $training37->equipments()->attach([$poidsCorporel->id]);

        // EXERCICES COMPOSÉS SUPPLÉMENTAIRES
        $training38 = Training::create([
            'name' => 'Burpees',
            'description' => 'Exercice fonctionnel complet : squat, planche, pompe, saut. Excellent pour le cardio et le renforcement général.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training38->categories()->attach([$quadriceps->id, $pectoraux->id, $triceps->id, $deltoides->id, $abdominaux->id]);
        $training38->equipments()->attach([$poidsCorporel->id]);

        $training39 = Training::create([
            'name' => 'Mountain Climbers',
            'description' => 'Position de planche avec alternance rapide des genoux vers la poitrine. Cardio intense et gainage.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training39->categories()->attach([$abdominaux->id, $deltoides->id, $quadriceps->id]);
        $training39->equipments()->attach([$poidsCorporel->id]);

        $training40 = Training::create([
            'name' => 'Kettlebell Swing',
            'description' => 'Balancement de kettlebell entre les jambes puis projection vers l\'avant. Excellent pour les fessiers et le cardio.',
            'image' => null,
            'video' => null,
            'user_id' => null,
        ]);
        $training40->categories()->attach([$fessier->id, $ishio->id, $deltoides->id, $abdominaux->id]);
        $training40->equipments()->attach([$kettlebells->id]);
    }
} 