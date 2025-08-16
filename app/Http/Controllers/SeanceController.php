<?php

namespace App\Http\Controllers;

use App\Models\Seance;
use App\Models\Training;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class SeanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexSeance(Request $request)
    {
        $query = Seance::with(['admin', 'coach', 'challenger', 'trainings'])
            ->withCount('trainings');

        // Recherche
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                  ->orWhereHas('challenger', function($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%")
                               ->orWhere('email', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('coach', function($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%")
                               ->orWhere('email', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('admin', function($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', "%{$search}%")
                               ->orWhere('email', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Tri
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Validation des champs de tri
        $allowedSortFields = ['id', 'created_at', 'updated_at', 'validated', 'admin_id', 'coach_id', 'challenger_id', 'trainings_count'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'id';
        }
        
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        // Tri spécial pour trainings_count
        if ($sortBy === 'trainings_count') {
            $query->orderBy('trainings_count', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $seances = $query->paginate($perPage);
            
        return response()->json([
            'seances' => $seances->items(),
            'pagination' => [
                'current_page' => $seances->currentPage(),
                'last_page' => $seances->lastPage(),
                'per_page' => $seances->perPage(),
                'total' => $seances->total(),
                'from' => $seances->firstItem(),
                'to' => $seances->lastItem(),
            ]
        ], 200);
    }
    public function indexCoach()
    {
        $seances = Auth::user()->coachSeances;
        return response()->json([
            'seances' => $seances,
        ], 201);
    }
    public function indexChallenger()
    {
        $users = User::all();
        $seances = Auth::user()->challengerSeances;
        return response()->json([
            'seances' => $seances,
            'users' => $users
        ], 201);
    }

    /**
     * Generate an AI-like suggested plan for the next seance for the current challenger.
     */
    public function generateAiPlanChallenger(Request $request)
    {
        $userId = Auth::id();
        $availableTime = (int) $request->get('available_time', 60);

        // Récupérer les trainings disponibles
        $trainings = Training::with('categories')->get();
        if ($trainings->isEmpty()) {
            return response()->json([
                'plan' => [],
                'message' => 'Aucun exercice disponible'
            ], 200);
        }

        // Récupérer les 2-3 dernières séances du challenger
        $recentSeances = Seance::where('challenger_id', $userId)
            ->with(['trainings.categories'])
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        // Essayer l'IA Python (workout_generator.py --plan)
        try {
            $scriptPath = base_path('app/Http/Controllers/IA/workout_generator.py');
            $process = new Process(['/home/happy/Documents/PERSO/HPFit/.venv/bin/python', $scriptPath, '--plan']);
            $process->setTimeout(30);

            // User profile minimal (pas de valeur par défaut ici)
            $user = Auth::user();
            $userProfile = [
                'age' => data_get($user, 'age'),
                'weight_kg' => data_get($user, 'weight_kg'),
                'height_cm' => data_get($user, 'height_cm'),
                'fitness_level' => data_get($user, 'fitness_level'),
                'goal' => data_get($user, 'goal'),
                'available_time' => $availableTime,
                'equipment' => data_get($user, 'equipment', []),
            ];
            if (!is_array($userProfile['equipment'])) {
                $userProfile['equipment'] = [];
            }
            $userProfile = array_filter($userProfile, function($v) { return !is_null($v) && $v !== ''; });

            // Trainings et séances récentes formatés pour le script
            $trainingsArray = $trainings->map(function($t) {
                return [
                    'id' => $t->id,
                    'name' => $t->name,
                    'categories' => ($t->categories ? $t->categories->map(function($c){ return ['name' => $c->name]; })->values()->all() : []),
                ];
            })->values()->all();

            $recentArray = $recentSeances->map(function($s) {
                return [
                    'id' => $s->id,
                    'created_at' => (string) $s->created_at,
                    'trainings' => $s->trainings->map(function($tr){
                        return [
                            'id' => $tr->id,
                            'name' => $tr->name,
                            'categories' => ($tr->categories ? $tr->categories->map(function($c){ return ['name' => $c->name]; })->values()->all() : []),
                        ];
                    })->values()->all(),
                ];
            })->values()->all();

            $payload = json_encode([
                'user' => $userProfile,
                'recent_seances' => $recentArray,
                'trainings' => $trainingsArray,
                'available_time' => $availableTime,
            ], JSON_UNESCAPED_UNICODE);

            // Passer le JSON en stdin et l'API Key en env
            $process->setInput($payload);
            $process->setEnv(array_merge($_ENV, $_SERVER, [
                'OPENAI_API_KEY' => config('services.openai.key'),
            ]));

            $process->run();

            $output = $process->getOutput();
            $errorOutput = $process->getErrorOutput();

            // Tenter de décoder la sortie comme JSON (succès ou erreur)
            $decoded = json_decode($output ?: '{}', true);

            if ($process->isSuccessful()) {
                if (json_last_error() === JSON_ERROR_NONE && isset($decoded['plan']) && is_array($decoded['plan'])) {
                    return response()->json([
                        'plan' => $decoded['plan'],
                    ], 200);
                }
                Log::warning('AI planner returned invalid JSON structure', ['output' => $output]);
                return response()->json([
                    'error' => 'Réponse IA invalide',
                    'details' => $output,
                ], 502);
            }

            // Process non successful: retourner la raison
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['error'])) {
                return response()->json([
                    'error' => 'Échec génération IA',
                    'details' => $decoded['error'],
                ], 502);
            }

            return response()->json([
                'error' => 'Process IA échoué',
                'details' => trim($errorOutput) ?: 'Raison inconnue',
            ], 502);
        } catch (\Throwable $e) {
            Log::warning('AI planner exception', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Exception lors de la génération IA',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeChallenger(Request $request)
    {
        $seance = new Seance();
        $seance->challenger_id = Auth::user()->id;
        if($request->hasFile('img_debut')){
            $image_name = $request->img_debut->getClientOriginalName();
            $urlimg = $request->img_debut->storeAs('seance/'.Auth::user()->id , $image_name , 'public');
            $seance->img_debut = $image_name;
        }
        if($request->hasFile('img_fin')){
            $image_name = $request->img_fin->getClientOriginalName();
            $urlimg = $request->img_fin->storeAs('seance/'.Auth::user()->id , $image_name , 'public');
            $seance->img_fin = $image_name;
        }
        $seance->validated = true;
        $seance->save();
        $trainings = $request->input('traininglist');
        $data = [];
        for ($i = 0; $i < count($trainings); $i++) {
            $seance->trainings()->attach($request['traininglist'][$i], [
                'series' => $request['series'][$i],
                'repetitions' => $request['repetitions'][$i],
                'duree' => $request['duree'][$i],
            ]);
        }
        return response()->json([
            'message' => "La séance a bien été créé.",
        ], 201);
    }

    /**
     * Challenger requests coaching: create an empty séance with only the challenger set.
     */
    public function requestCoachingChallenger(Request $request)
    {
        $seance = new Seance();
        $seance->challenger_id = Auth::id();
        $seance->admin_id = null; // Will be claimed by an admin when assigning a coach
        $seance->coach_id = null; // Not assigned yet
        $seance->validated = null; // Pending/assigned state
        $seance->save();

        return response()->json([
            'message' => "Votre demande de coaching a été créée. Un coach vous sera assigné.",
            'seance' => $seance,
        ], 201);
    }
    public function assignSeanceCoachChallenger(Request $request, User $coach)
    { //id de coach
        $admin = Auth::user();
        $coach = $coach;
        $challenger = User::find($request->challenger_id);
        $sceance = new Seance;
        $sceance->admin_id = $admin->id;
        $sceance->coach_id = $coach->id;
        $sceance->challenger_id = $challenger->id;
        $sceance->validated = null; // Séance assignée par défaut
        $sceance->save();
        return response()->json([
            'message' => "La séance a bien été créé.",
        ], 201);
    }
    public function assignSeanceChallengerCoach(Request $request, User $challenger)
    { //id de coach
        $admin = Auth::user();
        $challenger = $challenger;
        $coach = User::find($request->coach_id);
        $sceance = new Seance;
        $sceance->admin_id = $admin->id;
        $sceance->coach_id = $coach->id;
        $sceance->challenger_id = $challenger->id;
        $sceance->validated = null; // Séance assignée par défaut
        $sceance->save();
        return response()->json([
            'message' => "La séance a bien été créé.",
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Seance $seance)
    {
        return response()->json([
            'seance' => $seance,
            'admin' => $seance->admin,
            'coach' => $seance->coach,
            'challenger' => $seance->challenger,
            'trainings' => $seance->trainings->load('categories'),
            'myId' => Auth::user()->id,
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Seance $seance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateImgDebut(Request $request, Seance $seance)
    {
        $this->validate($request, [
            'img_debut' => 'image',
        ]);
        if($request->hasFile('img_debut')){
            $image_name = $request->img_debut->getClientOriginalName();
            $urlimg = $request->img_debut->storeAs('seance/'.$seance->challenger->id , $image_name , 'public');
            //$urlimg = $request->img_debut->storeAs('seance' , $image_name , 'public');
            $seance->img_debut = $image_name;
            $seance->save();
            return response()->json([
                'message' => "L'image a bien été modifiée.",
            ], 200);
        }
        return response()->json([
            'message' => $request->img_debut,
        ], 200);
    }
    public function updateSupprImgDebut(Request $request, Seance $seance){
        $seance->img_debut = $request['suppr'];
        $seance->save();
        return response()->json([
            'message' => "image removed.",
        ], 200);
    }
    public function updateSupprImgFin(Request $request, Seance $seance){
        $seance->img_fin = $request['suppr'];
        $seance->save();
        return response()->json([
            'message' => "image removed.",
        ], 200);
    }
    public function updateImgFin(Request $request, Seance $seance)
    {
        $this->validate($request, [
            'img_fin' => 'image',
        ]);
        if($request->hasFile('img_fin')){
            $image_name = $request->img_fin->getClientOriginalName();
            $urlimg = $request->img_fin->storeAs('seance/'.$seance->challenger->id , $image_name , 'public');
            $seance->img_fin = $image_name;
            $seance->save();
            return response()->json([
                'message' => "L'image a bien été modifiée.",
            ], 200);
        }
        return response()->json([
            'message' => "Echec.",
        ], 200);
    }

    /*Challenger debut image*/


    public function updateChallengerDebut(Request $request, Seance $seance)
    {
        $this->validate($request, [
            'img_debut' => 'image',
        ]);
        if($request->hasFile('img_debut')){
            $image_name = $request->img_debut->getClientOriginalName();
            $urlimg = $request->img_debut->storeAs('seance/'.$seance->challenger->id , $image_name , 'public');
            //$urlimg = $request->img_debut->storeAs('seance' , $image_name , 'public');
            $seance->img_debut = $image_name;
            $seance->save();
            return response()->json([
                'message' => "L'image a bien été modifiée.",
            ], 200);
        }
        return response()->json([
            'message' => $request->img_debut,
        ], 200);
    }
    public function updateSupprChallengerDebut(Request $request, Seance $seance)
    {
        $seance->img_debut = $request['suppr'];
        $seance->save();
        return response()->json([
            'message' => "image removed.",
        ], 200);
    }
    public function updateSupprChallengerFin(Request $request, Seance $seance){
        $seance->img_fin = $request['suppr'];
        $seance->save();
        return response()->json([
            'message' => "image removed.",
        ], 200);
    }
    public function updateChallengerFin(Request $request, Seance $seance)
    {
        $this->validate($request, [
            'img_fin' => 'image',
        ]);
        if($request->hasFile('img_fin')){
            $image_name = $request->img_fin->getClientOriginalName();
            $urlimg = $request->img_fin->storeAs('seance/'.$seance->challenger->id , $image_name , 'public');
            $seance->img_fin = $image_name;
            $seance->save();
            return response()->json([
                'message' => "L'image a bien été modifiée.",
            ], 200);
        }
        return response()->json([
            'message' => "Echec.",
        ], 200);
    }

    /*challenger fin*/

    public function updateValider(Seance $seance)
    {
        $seance->validated = false;
        $seance->save();
        return response()->json([
            'message' => "Le challenger a bien été notifié de la séance.",
        ], 200);
    }
    public function updateConfirmer(Seance $seance)
    {
        $seance->validated = true;
        $seance->save();
        return response()->json([
            'mesage' => "Félicitation vous avez terminé votre séance.",
        ], 200);
    }
    public function updateDecliner(Seance $seance)
    {
        $seance->validated = false;
        $seance->save();
        return response()->json([
            'message' => "La séance a été déclinée car une erreur a été constatée.",
        ], 200);
    }

    /**
     * Manage trainings of the Seance.
     */
    public function addTrainings(Request $request, Seance $seance)
    {
        $trainings = $request->input('traininglist');
        $data = [];
        for ($i = 0; $i < count($trainings); $i++) {
            $seance->trainings()->attach($request['traininglist'][$i], [
                'series' => $request['series'][$i],
                'repetitions' => $request['repetitions'][$i],
                'duree' => $request['duree'][$i],
            ]);
        }
        return response()->json([
            'mesage' => "Vous avez bien ajouté des exercices a la séance.",
        ], 200);
    }
    public function addTrainingsChallenger(Request $request, Seance $seance)
    {
        $trainings = $request->input('traininglist');
        $data = [];
        for ($i = 0; $i < count($trainings); $i++) {
            $seance->trainings()->attach($request['traininglist'][$i], [
                'series' => $request['series'][$i],
                'repetitions' => $request['repetitions'][$i],
                'duree' => $request['duree'][$i],
            ]);
        }
        return response()->json([
            'mesage' => "Vous avez bien ajouté des exercices a la séance.",
        ], 200);
    }
    public function updateTrainings(Request $request, Seance $seance)
    {
        foreach ($seance->trainings as $key => $training) {
            $pivotId = $training->pivot->id;
            $training->pivot->update([
                'training_id' => $request->input('traininglist')[$key],
                'series' => $request->input('series')[$key],
                'repetitions' => $request->input('repetitions')[$key],
                'duree' => $request->input('duree')[$key],
            ]);
        }
        return response()->json([
            'mesage' => "Les exercices de la séance a bien été mise à jour.",
        ], 200);
    }
    public function deleteTraining(Seance $seance, Training $training, $id)
    {
        $seance->trainings()->wherePivot('id', $id)->detach($training->id);
        //$seance->trainings()->wherePivot('id', $id)->detach();
        if($seance->trainings->count() > 0){
            return response()->json([
                'message' => "Vous avez bien supprimé l'exercice.",
            ], 200);
        }
        return response()->json([
            'message' => "Vous avez bien supprimé l'exercice, la scéance ne contient plus d'exercice.",
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyChallengerSeance(Seance $seance)
    {
        $seance->trainings()->detach();
        $seance->delete();
        return response()->json([
            'mesage' => "Vous avez bien supprimé la séance.",
        ], 200);
    }
    public function destroySeance(Seance $seance)
    {
        // Seul l'admin qui a créé la séance peut la supprimer
        if ($seance->admin_id !== Auth::user()->id) {
            return response()->json([
                'message' => "Vous n'êtes pas autorisé à supprimer cette séance."
            ], 403);
        }

        // On ne peut supprimer que les séances encore assignées (validated = null)
        if ($seance->validated !== null) {
            return response()->json([
                'message' => "Cette séance ne peut plus être supprimée car elle est en cours de traitement."
            ], 422);
        }

        $seance->trainings()->detach();
        $seance->delete();
        
        return response()->json([
            'message' => "Vous avez bien supprimé la séance.",
        ], 200);
    }

    /**
     * Store a newly created resource in storage (Admin).
     */
    public function storeSeance(Request $request)
    {
        $request->validate([
            'coach_id' => 'required|exists:users,id',
            'challenger_id' => 'required|exists:users,id',
        ]);

        $seance = new Seance();
        $seance->admin_id = Auth::user()->id;
        $seance->coach_id = $request->coach_id;
        $seance->challenger_id = $request->challenger_id;
        $seance->validated = null; // Séance assignée par défaut
        $seance->save();

        return response()->json([
            'message' => "La séance a bien été créée et assignée.",
            'seance' => $seance
        ], 201);
    }

    /**
     * Update the specified resource in storage (Admin).
     */
    public function updateSeance(Request $request, Seance $seance)
    {
        $request->validate([
            'coach_id' => 'nullable|exists:users,id',
            'challenger_id' => 'required|exists:users,id',
        ]);

        // Si aucune admin n'est encore assigné, l'admin courant prend la propriété
        if ($seance->admin_id === null) {
            $seance->admin_id = Auth::user()->id;
        } elseif ($seance->admin_id !== Auth::user()->id) {
            // Sinon, seul l'admin qui a créé la séance peut la modifier
            return response()->json([
                'message' => "Vous n'êtes pas autorisé à modifier cette séance."
            ], 403);
        }

        // On ne peut modifier que les séances encore assignées (validated = null)
        if ($seance->validated !== null) {
            return response()->json([
                'message' => "Cette séance ne peut plus être modifiée car elle est en cours de traitement."
            ], 422);
        }

        $seance->coach_id = $request->coach_id;
        $seance->challenger_id = $request->challenger_id;
        $seance->save();

        return response()->json([
            'message' => "La séance a bien été mise à jour.",
            'seance' => $seance
        ], 200);
    }
}
