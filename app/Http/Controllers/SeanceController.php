<?php

namespace App\Http\Controllers;

use App\Models\Seance;
use App\Models\Training;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexSeance()
    {
        $seances = Seance::all();
        return response()->json([
            'seances' => $seances,
        ], 201);
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
    public function assignSeanceCoachChallenger(Request $request, User $coach)
    { //id de coach
        $admin = Auth::user();
        $coach = $coach;
        $challenger = User::find($request->challenger_id);
        $sceance = new Seance;
        $sceance->admin_id = $admin->id;
        $sceance->coach_id = $coach->id;
        $sceance->challenger_id = $challenger->id;
        //$sceance->validated = false;
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
        //$sceance->validated = false;
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
        $seance->validated = null;
        $seance->save();
        return response()->json([
            'mesage' => "Félicitation vous avez terminé votre séance.",
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
        $seance->trainings()->detach();
        $seance->delete();
        return response()->json([
            'message' => "Vous avez bien supprimé la séance.",
        ], 200);
    }
}
