<?php

namespace App\Http\Controllers;

use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    //------------------------- SHOW ALL ------------------------------//
    public function index()
    {
        $produits = Produit::all()->load('advices');
        $produits->each(function ($produit) {
            $produit->rating = $produit->getAverageRatingAttribute();
        });
        return response()->json([
            'produits' => $produits,
        ], 201);
    }

    /**
     * Store a newly created resource in storage.
     */
    //------------------------- CREATE ------------------------------//
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'image' => 'required|image',
            'description' => 'required',
            'poid' => 'required',
            'price' => 'required',
        ]);
        if ($request->hasFile('image')) {
            $filename = $request->image->getClientOriginalName();
            $urlimg = $request->image->storeAs('produits' , $filename , 'public');
            Produit::create([
                'name' => $request->name,
                'image' => $filename,
                'description' => $request->description,
                'poid' => $request->poid,
                'price' => $request->price,
                'user_id' => Auth::user()->id,
            ]);
            return response()->json([
                'message' => 'Le produit a bien été créé.'
            ], 200);
        }
        Produit::create([
            'name' => $request->name,
            'description' => $request->description,
            'poid' => $request->poid,
            'price' => $request->price,
            'user_id' => Auth::user()->id,
        ]);
        return response()->json([
            'message' => 'Le produit a bien été créé.'
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    //------------------------- SHOW ONE ------------------------------//
    public function show(Produit $produit)
    {
        return response()->json([
            'produit' => $produit,
            'note' => $produit->getAverageRatingAttribute(),
            'avis' => $produit->advices,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    //------------------------- UPDATE ------------------------------//
    public function update(Request $request, Produit $produit)
    {
        $this->validate($request, [
            'name' => 'required',
            'image' => 'image',
            'description' => 'required',
            'poid' => 'required',
            'price' => 'required',
        ]);
        if ($request->hasFile('image')) {
            $filename = $request->image->getClientOriginalName();
            $urlimg = $request->image->storeAs('produits' , $filename , 'public');
            $produit->image = $filename;
        }
        $produit->name = $request['name'];
        $produit->description = $request['description'];
        $produit->poid = $request['poid'];
        $produit->price = $request['price'];
        $produit->save();
        return response()->json([
            'message' => "Le produit a bien été modifié."
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    //------------------------- DELETE ------------------------------//
    public function destroy(Produit $produit)
    {
        $produit->delete();
        return response()->json([
            'message' => "Le produit a bien été supprimé."
        ], 204);
    }
}
