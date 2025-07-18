<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Training::with(['categories', 'equipments']);

        // Recherche
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('categories', function($categoryQuery) use ($search) {
                      $categoryQuery->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('equipments', function($equipmentQuery) use ($search) {
                      $equipmentQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Tri
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'asc');
        
        // Valider les colonnes de tri autorisées
        $allowedSortColumns = ['id', 'name', 'description', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('id', 'asc');
        }

        $trainings = $query->paginate(10);
        return response()->json($trainings, 200);
    } 
    public function indexChallenger()
    {
        $trainings = Training::with(['categories', 'equipments'])->get();
        return response()->json([
            'trainings' => $trainings,
        ], 200);
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
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'description' => 'required',
            'image' => 'nullable|image',
            'video' => 'nullable',
            'categories' => 'required|array',
            'equipments' => 'nullable|array'
        ]);
        
        $image_name = null;
        if($request->hasFile('image')){
            $image_name = $request->image->getClientOriginalName();
            $urlimg = $request->image->storeAs('trainings' , $image_name , 'public');
        }
        
        $video_name = null;
        if($request->hasFile('video')){
            $video_name = $request->video->getClientOriginalName();
            $urlvid = $request->video->storeAs('training_videos' , $video_name , 'public');
        }
        
        $training = Training::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $image_name,
            'video' => $video_name,
            'user_id' => Auth::user()->id,
        ]);

        // Attacher les catégories
        $training->categories()->attach($request->categories);
        
        // Attacher les équipements si fournis
        if ($request->has('equipments') && !empty($request->equipments)) {
            $training->equipments()->attach($request->equipments);
        }

        return response()->json([
            'message' => "L'exercice a bien été créé.",
            'training' => $training->load(['categories', 'equipments'])
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Training $training)
    {
        // Charger toutes les relations nécessaires
        $training->load(['categories', 'equipments']);
        
        // Récupérer des suggestions de trainings similaires
        // Basé sur les catégories communes
        $categoryIds = $training->categories->pluck('id')->toArray();
        
        $suggestions = Training::where('id', '!=', $training->id)
            ->whereHas('categories', function($query) use ($categoryIds) {
                $query->whereIn('categories.id', $categoryIds);
            })
            ->with(['categories', 'equipments'])
            ->inRandomOrder()
            ->limit(8) // 8 suggestions pour avoir plus de choix
            ->get();
        
        // Si pas assez de suggestions basées sur les catégories, compléter avec des trainings aléatoires
        if ($suggestions->count() < 8) {
            $additionalSuggestions = Training::where('id', '!=', $training->id)
                ->whereNotIn('id', $suggestions->pluck('id')->toArray())
                ->with(['categories', 'equipments'])
                ->inRandomOrder()
                ->limit(8 - $suggestions->count())
                ->get();
            
            $suggestions = $suggestions->merge($additionalSuggestions);
        }

        return response()->json([
            'training' => $training,
            'categories' => $training->categories,
            'equipments' => $training->equipments,
            'suggestions' => $suggestions,
        ], 200);
    }
    
    public function showChallenger(Training $training)
    {
        return response()->json([
            'training' => $training,
            'categories' => $training->categories,
            'equipments' => $training->equipments,
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Training $training)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Training $training)
    {
        $this->validate($request, [
            'name' => 'required',
            'description' => 'required',
            'image' => 'nullable|image',
            'video' => 'nullable|mimetypes:video/mp4',
            'categories' => 'required|array',
            'equipments' => 'nullable|array'
        ]);
        
        $training->name = $request->name;
        $training->description = $request->description;

        if($request->hasFile('image')){
            $image_name = $request->image->getClientOriginalName();
            $urlimg = $request->image->storeAs('trainings' , $image_name , 'public');
            $training->image = $image_name;
        }

        if($request->hasFile('video')){
            $video_name = $request->video->getClientOriginalName();
            $urlvid = $request->video->storeAs('training_videos' , $video_name , 'public');
            $training->video = $video_name;
        }
        
        // Mettre à jour les catégories
        $training->categories()->detach();
        $training->categories()->attach($request->categories);

        // Mettre à jour les équipements
        $training->equipments()->detach();
        if ($request->has('equipments') && !empty($request->equipments)) {
            $training->equipments()->attach($request->equipments);
        }

        $training->save();
        
        return response()->json([
            'message' => "L'exercice a bien été modifié.",
            'training' => $training->load(['categories', 'equipments'])
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Training $training)
    {
        $training->categories()->detach();
        $training->equipments()->detach();
        $training->user()->dissociate();
        $training->delete();
        
        return response()->json([
            'message' => "L'exercice a bien été supprimé."
        ], 200);
    }
}
