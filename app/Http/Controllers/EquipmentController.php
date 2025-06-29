<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Training;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    /**
     * Récupérer tous les équipements
     */
    public function index(Request $request)
    {
        $query = Equipment::query();

        // Recherche
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
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

        $equipments = $query->paginate(10);
        return response()->json($equipments, 200);
    }

    /**
     * Récupérer tous les trainings utilisant un équipement spécifique
     */
    public function getTrainingsByEquipment(Equipment $equipment)
    {
        $trainings = $equipment->trainings()->with(['categories', 'equipments'])->get();
        
        return response()->json([
            'equipment' => $equipment,
            'trainings' => $trainings,
        ], 200);
    }

    /**
     * Afficher un équipement spécifique
     */
    public function show(Equipment $equipment)
    {
        // Charger l'équipement avec ses trainings et leurs relations
        $equipment->load(['trainings.categories', 'trainings.equipments']);
        
        return response()->json([
            'equipment' => $equipment,
        ], 200);
    }

    /**
     * Créer un nouvel équipement (pour les utilisateurs personnalisés)
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image',
        ]);

        $image_name = null;
        if($request->hasFile('image')){
            $image_name = $request->image->getClientOriginalName();
            $urlimg = $request->image->storeAs('equipments', $image_name, 'public');
        }

        $equipment = Equipment::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $image_name,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => "L'équipement a bien été créé.",
            'equipment' => $equipment,
        ], 201);
    }

    /**
     * Mettre à jour un équipement
     */
    public function update(Request $request, Equipment $equipment)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image',
        ]);

        $equipment->name = $request->name;
        $equipment->description = $request->description;

        if($request->hasFile('image')){
            $image_name = $request->image->getClientOriginalName();
            $urlimg = $request->image->storeAs('equipments', $image_name, 'public');
            $equipment->image = $image_name;
        }

        $equipment->save();

        return response()->json([
            'message' => "L'équipement a bien été modifié.",
            'equipment' => $equipment,
        ], 200);
    }

    /**
     * Supprimer un équipement
     */
    public function destroy(Equipment $equipment)
    {
        // Détacher l'équipement de tous les trainings
        $equipment->trainings()->detach();
        $equipment->delete();

        return response()->json([
            'message' => "L'équipement a bien été supprimé."
        ], 200);
    }
} 