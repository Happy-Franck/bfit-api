<?php

namespace App\Http\Controllers;

use App\Models\Advice;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(Request $request, Produit $produit)
    {
        //$produit = Produit::findOrFail($produitId);
        $user = Auth::user();
        // Vérifier si l'utilisateur a déjà donné un avis pour ce livre
        $existingAdvice = $produit->advices()->where('user_id', $user->id)->exists();
        $validatedData = $request->validate([
            'comment' => 'string|max:1000',
            'note' => 'integer|between:1,5',
        ]);
        if($existingAdvice) {
            return response()->json([
                'message' => 'Vous avez déjà mis un commentaire.'
            ], 200);
        }
        $validatedData['user_id'] = $user->id;
        $validatedData['produit_id'] = $produit->id;
        Advice::create($validatedData);
        return response()->json([
            'message' => 'Commentaire envoyé.'
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Advice $advice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Advice $advice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produit $produit, Advice $advice)
    {
        //$advice = Advice::findOrFail($id);
        $user = Auth::user();
        $validatedData = $request->validate([
            'comment' => 'required|string|max:1000',
            'note' => 'required|integer|between:1,5',
        ]);
        // Vérifier si l'utilisateur est l'auteur de cet avis
        if ($advice->user_id !== $user->id) {
            return response()->json([
                'message' => "vous n'avez pas l'autorisation de modifier ce commentaire."
            ], 403);
        }
        $advice->update($validatedData);
        return response()->json([
            'message' => "Le commentaire a bien été modifié."
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produit $produit, Advice $advice)
    {
        //$advice = Advice::findOrFail($id);
        $user = Auth::user();
        // Vérifier si l'utilisateur est l'auteur de cet avis
        if ($advice->user_id !== $user->id) {
            return response()->json([
                'message' => "vous n'avez pas l'autorisation de supprimer ce commentaire."
            ], 403);
        }
        $advice->delete();
        return response()->json([
            'message' => "Votre commentaire a bien été supprimé."
        ], 204);
    }
}
