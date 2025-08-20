<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Sceance;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles'])
            ->withCount([
                'coachSeances as seances_as_coach_count',
                'challengerSeances as seances_as_challenger_count'
            ])
            ->get();
            
        return response()->json([
            'users' => $users,
        ], 201);
    }
    public function coach()
    {
        $coachs = User::role('coach')->get();
        return response()->json([
            'coachs' => $coachs,
        ], 201);
    }
    public function challenger()
    {
        $challengers = User::role('challenger')->get();
        return response()->json([
            'challengers' => $challengers,
        ], 201);
    }

    public function show(User $user)
    {
        $user->load(['roles', 'permissions']);
        
        // Charger les comptes de séances
        $user->loadCount([
            'coachSeances as seances_as_coach_count',
            'challengerSeances as seances_as_challenger_count'
        ]);

        // Préparer les données des séances selon le rôle
        $seancesData = [];
        
        if($user->hasRole('coach')){
            // Pour un coach, on récupère ses séances avec les challengers coachés
            $seances = $user->coachSeances()->with([
                'challenger:id,name,avatar', 
                'trainings.categories:id,name',
                'trainings.equipments:id,name'
            ])->get();
            
            foreach($seances as $seance) {
                $musclesTravailles = $seance->trainings
                    ->flatMap(function($training) {
                        return $training->categories->pluck('name');
                    })
                    ->unique()
                    ->filter()
                    ->values()
                    ->toArray();
                    
                $seancesData[] = [
                    'id' => $seance->id,
                    'date_seance' => $seance->created_at,
                    'muscles_travailles' => $musclesTravailles,
                    'nombre_exercices' => $seance->trainings->count(),
                    'challenger' => [
                        'id' => $seance->challenger?->id,
                        'name' => $seance->challenger?->name,
                        'avatar' => $seance->challenger?->avatar
                    ],
                    'state' => $seance->validated,
                    'type' => 'coach' // Indique le point de vue
                ];
            }
        }
        
        if($user->hasRole('challenger')){
            // Pour un challenger, on récupère ses séances avec les coachs
            $seances = $user->challengerSeances()->with([
                'coach:id,name,avatar',
                'trainings.categories:id,name',
                'trainings.equipments:id,name'
            ])->get();
            
            foreach($seances as $seance) {
                $musclesTravailles = $seance->trainings
                    ->flatMap(function($training) {
                        return $training->categories->pluck('name');
                    })
                    ->unique()
                    ->filter()
                    ->values()
                    ->toArray();
                    
                $seancesData[] = [
                    'id' => $seance->id,
                    'date_seance' => $seance->created_at,
                    'muscles_travailles' => $musclesTravailles,
                    'nombre_exercices' => $seance->trainings->count(),
                    'coach' => [
                        'id' => $seance->coach?->id,
                        'name' => $seance->coach?->name,
                        'avatar' => $seance->coach?->avatar
                    ],
                    'state' => $seance->validated,
                    'type' => 'challenger' // Indique le point de vue
                ];
            }
        }
        
        if($user->hasRole('administrateur')) {
            // Pour un administrateur, on peut récupérer toutes ses séances
            $coachSeances = $user->coachSeances()->with([
                'challenger:id,name,avatar',
                'trainings.categories:id,name'
            ])->get();
            
            $challengerSeances = $user->challengerSeances()->with([
                'coach:id,name,avatar',
                'trainings.categories:id,name'
            ])->get();
            
            // Traiter les séances en tant que coach
            foreach($coachSeances as $seance) {
                $musclesTravailles = $seance->trainings
                    ->flatMap(function($training) {
                        return $training->categories->pluck('name');
                    })
                    ->unique()
                    ->filter()
                    ->values()
                    ->toArray();
                    
                $seancesData[] = [
                    'id' => $seance->id,
                    'date_seance' => $seance->created_at,
                    'muscles_travailles' => $musclesTravailles,
                    'nombre_exercices' => $seance->trainings->count(),
                    'challenger' => [
                        'id' => $seance->challenger?->id,
                        'name' => $seance->challenger?->name,
                        'avatar' => $seance->challenger?->avatar
                    ],
                    'state' => $seance->validated,
                    'type' => 'coach'
                ];
            }
            
            // Traiter les séances en tant que challenger
            foreach($challengerSeances as $seance) {
                $musclesTravailles = $seance->trainings
                    ->flatMap(function($training) {
                        return $training->categories->pluck('name');
                    })
                    ->unique()
                    ->filter()
                    ->values()
                    ->toArray();
                    
                $seancesData[] = [
                    'id' => $seance->id,
                    'date_seance' => $seance->created_at,
                    'muscles_travailles' => $musclesTravailles,
                    'nombre_exercices' => $seance->trainings->count(),
                    'coach' => [
                        'id' => $seance->coach?->id,
                        'name' => $seance->coach?->name,
                        'avatar' => $seance->coach?->avatar
                    ],
                    'state' => $seance->validated,
                    'type' => 'challenger'
                ];
            }
        }
        
        // Trier les séances par date décroissante
        usort($seancesData, function($a, $b) {
            return strtotime($b['date_seance']) - strtotime($a['date_seance']);
        });
        
        return response()->json([
            'user' => $user,
            'roles' => $user->roles,
            'permissions' => $user->permissions,
            'challengers' => $user->challengers,
            'seances' => $seancesData, // Nouvelles données structurées des séances
            'coachs' => $user->coachs,
        ], 200);
    }

    public function destroy(User $user)
    {
        if($user->hasRole('administrateur')){
            return response()->json([
                'message' => "Les administrateurs ne peuvent pas être supprimé."
            ], 200);
        }
        $user->delete();
        return response()->json([
            'message' => "L'utilisateur a été supprimé."
        ], 201);
    }

    public function assignRole(Request $request, User $user)
    {
        //$user->roles->dissociate();
        //$user->roles->detach();
        $user->syncRoles([]);
        /*if($user->hasRole($request->role)){
            return response()->json([
                'message' => "L'user a déjà ce rôle."
            ], 200);
        }*/
        $user->assignRole($request->role);
        return response()->json([
            'message' => "Les rôles de l'utilisateur a été mise à jour."
        ], 200);
    }

    public function removeRole(User $user, Role $role)
    {
        if($user->hasRole($role)){
            $user->removeRole($role);
            return response()->json([
                'message' => "Le rôle de l'utilisateur a été enlevé."
            ], 200);
        }
        return response()->json([
            'message' => "Cet utilisateur n'a pas ce rôle."
        ], 200);
    }

    public function givePermission(Request $request, User $user)
    {
        if($user->hasPermissionTo($request->permission) || $user->roles->contains($request->permission)){
            return response()->json([
                'message' => "Cet utilisateur a déjà det permission."
            ], 200);
        }
        $user->givePermissionTo($request->permission);
        return response()->json([
            'message' => "Les permissions de l'utilisateur a été mise à jour."
        ], 200);
    }

    public function revokePermission(User $user, Permission $permission)
    {
        if($user->hasPermissionTo($permission)){
            $user->revokePermissionTo($permission);
            return response()->json([
                'message' => "Les permission de l'utilisateur a été mise à jour."
            ], 200);
        }
        return response()->json([
            'message' => "Cet utilisateur n'a pas cette permission."
        ], 200);
    }

    public function removeChallenger(User $coach, User $challenger)
    {
        $coach->challengers()->detach($challenger);
        return response()->json([
            'message' => 'les challengers du coach ont été mise à jour'
        ], 200);
    }

    public function updateChallengers(Request $request, User $user)
    {
        $newChallengers = $request->input('new_challengers', []);
        if (!empty($newChallengers)) {
            //efface tout les anciens
            //$user->coachedChallengers()->sync($newChallengers);
            //crèe de nouveaux
            foreach ($newChallengers as $challengerId) {
                $user->challengers()->attach($challengerId);
            }
            return response()->json([
                'message' => 'les challengers du coach ont été mise à jour'
            ], 200);
        }
        return response()->json([
            'message' => "Une erreur s'est produit."
        ], 200);
    }

    public function removeCoach(User $challenger, User $coach)
    {
        $challenger->coachs()->detach($coach);
        return response()->json([
            'message' => 'les coach du challenger ont été mise à jour'
        ], 200);
    }

    public function updateCoachs(Request $request, User $user)
    {
        $newCoaches = $request->input('new_coachs', []);
        // Si on veut assigner de nouveaux coachs
        if (!empty($newCoaches)) {
            //$user->coaches()->sync($newCoaches);
            foreach ($newCoaches as $coachId) {
                $user->coachs()->attach($coachId);
            }
            return response()->json([
                'message' => 'les coach du challenger ont été mise à jour'
            ], 200);
        }
        return response()->json([
            'message' => "Une erreur s'est produit."
        ], 200);
    }

    public function myChallengers(User $user)
    {
        return response()->json([
            'challengers' => $user->challengers,
        ], 200);
    }
    public function coachChallengers(){
        // Récupérer tous les challengers avec leurs séances
        $allChallengers = User::role('challenger')->with('challengerSeances')->get();
        $myChallengers = Auth::user()->challengers()->with('challengerSeances')->get();
        
        // Fonction pour calculer les statistiques d'un challenger
        $calculateStats = function($challenger) {
            $seances = $challenger->challengerSeances;
            
            // Séances totales
            $totalSessions = $seances->count();
            
            // Séances du mois en cours
            $monthlySessions = $seances->where('created_at', '>=', now()->startOfMonth())->count();
            
            // Séances solo (sans coach_id)
            $soloSessions = $seances->whereNull('coach_id')->count();
            
            return [
                'id' => $challenger->id,
                'name' => $challenger->name,
                'email' => $challenger->email,
                'avatar' => $challenger->avatar,
                'image' => $challenger->avatar, // Pour compatibilité avec le frontend
                'role' => $challenger->roles->first()?->name ?? 'Challenger',
                'totalSessions' => $totalSessions,
                'monthlySessions' => $monthlySessions,
                'soloSessions' => $soloSessions,
                'sessionsCompleted' => $monthlySessions, // Pour le calcul de productivité
            ];
        };
        
        // Appliquer les statistiques à tous les challengers
        $allChallengersWithStats = $allChallengers->map($calculateStats);
        $myChallengersWithStats = $myChallengers->map($calculateStats);
        
        return response()->json([
            'allChallengers' => $allChallengersWithStats,
            'myChallengers' => $myChallengersWithStats
        ]);
    }
    public function showChallenger(User $user){
        $challenger = $user->load('challengerSeances.trainings');
        
        // Calculer les exercices RÉALISÉS par jour de la semaine pour le graphique
        $seances = $challenger->challengerSeances;
        $exerciseCountByDay = [
            'Mon' => 0, 'Tue' => 0, 'Wed' => 0, 'Thu' => 0, 
            'Fri' => 0, 'Sat' => 0, 'Sun' => 0
        ];
        
        foreach($seances as $seance) {
            // On considère que la date de création de la séance = date de réalisation
            $seanceDate = $seance->created_at;
            $dayOfWeek = date('D', strtotime($seanceDate));
            
            // Compter seulement les exercices qui ont été assignés (trainings)
            $exerciseCount = $seance->trainings->count();
            
            // Debug: Log les informations de chaque séance
            \Log::info('Seance debug', [
                'seance_id' => $seance->id,
                'created_at' => $seanceDate,
                'day_of_week' => $dayOfWeek,
                'exercise_count' => $exerciseCount
            ]);
            
            // Mapper les jours anglais vers notre format
            $dayMap = [
                'Mon' => 'Mon', 'Tue' => 'Tue', 'Wed' => 'Wed', 'Thu' => 'Thu',
                'Fri' => 'Fri', 'Sat' => 'Sat', 'Sun' => 'Sun'
            ];
            
            if (isset($dayMap[$dayOfWeek])) {
                $exerciseCountByDay[$dayMap[$dayOfWeek]] += $exerciseCount;
            }
        }
        
        // Debug: Log le résultat final
        \Log::info('Final exercise count by day', $exerciseCountByDay);
        
        return response()->json([
            'challenger' => $challenger,
            'exerciseCountByDay' => array_values($exerciseCountByDay) // Retourner seulement les valeurs
        ]);
    }

    public function myCoachs(User $user)
    {
        return response()->json([
            'coachs' => $user->coachs,
        ], 200);
    }

    /**
     * Obtenir les informations du profil de l'utilisateur connecté
     */
    public function getCurrentUser(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'user' => $user->load('roles', 'permissions'),
        ], 200);
    }

    /**
     * Obtenir le profil complet de l'utilisateur connecté
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $user->load('roles', 'permissions');
        
        return response()->json([
            'user' => $user,
            'roles' => $user->roles,
            'permissions' => $user->permissions,
            'poids_history' => $user->poids ?? [],
        ], 200);
    }

    /**
     * Mettre à jour le profil de l'utilisateur connecté
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        // Tableau pour stocker les données à mettre à jour
        $dataToUpdate = [];
        
        // Liste des champs modifiables (sauf avatar et nouveau_poids qui sont traités séparément)
        $updatableFields = [
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,' . $user->id,
            'telephone' => 'string|max:20',
            'cin' => 'string|unique:users,cin,' . $user->id,
            'taille' => 'numeric|min:0.5|max:3.0',
            'objectif' => 'in:prise de masse,perte de poids,maintien,prise de force,endurance,remise en forme,sèche,souplesse,rééducation,tonification,préparation physique,performance',
            'sexe' => 'in:homme,femme',
            'date_naissance' => 'date|before:today',
        ];
        
        // Valider et collecter les champs présents dans la requête
        foreach ($updatableFields as $field => $rule) {
            if ($request->has($field) && $request->input($field) !== null) {
                $request->validate([$field => $rule]);
                $dataToUpdate[$field] = $request->input($field);
            }
        }
        
        // Gestion de l'upload d'avatar
        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);
            
            \Log::info('Avatar upload détecté', [
                'original_name' => $request->file('avatar')->getClientOriginalName(),
                'size' => $request->file('avatar')->getSize(),
                'mime_type' => $request->file('avatar')->getMimeType()
            ]);
            
            // Supprimer l'ancien avatar s'il existe
            if ($user->avatar) {
                $oldAvatarPath = storage_path('app/public/avatars/' . $user->avatar);
                if (file_exists($oldAvatarPath)) {
                    unlink($oldAvatarPath);
                    \Log::info('Ancien avatar supprimé');
                }
            }
            
            // Stocker le nouveau fichier
            $image_name = $request->avatar->getClientOriginalName();
            $request->avatar->storeAs('avatars', $image_name, 'public');
            $dataToUpdate['avatar'] = $image_name;
            
            \Log::info('Nouvel avatar stocké', ['nom' => $image_name]);
        }
        
        // Gestion de l'historique de poids
        if ($request->has('nouveau_poids') && $request->nouveau_poids !== null) {
            $request->validate([
                'nouveau_poids' => 'numeric|min:20|max:300'
            ]);
            
            $currentPoids = $user->poids ?? [];
            $currentPoids[] = [
                'date' => now()->format('Y-m-d'),
                'valeur' => (float) $request->nouveau_poids
            ];
            $dataToUpdate['poids'] = $currentPoids;
        }
        
        // Mettre à jour l'utilisateur si on a des données
        if (!empty($dataToUpdate)) {
            $user->update($dataToUpdate);
            
            \Log::info('Profil mis à jour', [
                'user_id' => $user->id,
                'updated_fields' => array_keys($dataToUpdate)
            ]);
            
            return response()->json([
                'message' => 'Profil mis à jour avec succès',
                'user' => $user->fresh()->load('roles', 'permissions'),
                'updated_fields' => array_keys($dataToUpdate)
            ], 200);
        }
        
        return response()->json([
            'message' => 'Aucune donnée à mettre à jour',
            'user' => $user->load('roles', 'permissions')
        ], 200);
    }

    /**
     * Obtenir l'historique de poids de l'utilisateur
     */
    public function getWeightHistory(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'poids_history' => $user->poids ?? [],
        ], 200);
    }
}
