<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();
        return response()->json([
            'categories' => $categories,
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
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'image' => 'nullable|image',
        ]);
        $filename = null;
        if($request->hasFile('image')){
            $filename = $request->image->getClientOriginalName();
            $urlimg = $request->image->storeAs('categories' , $filename , 'public');
        }
        Category::create([
            'name' => $request->name,
            'image' => $filename,
            'user_id' => Auth::user()->id,
        ]);
        return response()->json([
            'message' => 'La catégorie a bien été créé.'
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $category->load('trainings');
        return response()->json([
            'category' => $category,
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $this->validate($request, [
            'name' => 'required',
            'image' => 'image',
        ]);
        if($request->hasFile('image')){
            $filename = $request->image->getClientOriginalName();
            $urlimg = $request->image->storeAs('categories' , $filename , 'public');
            $category->image = $filename;
        }
        $category->name = $request['name'];
        $category->save();
        return response()->json([
            'message' => "La catégorie a bien été modifié."
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json([
            'message' => "La catégorie a bien été supprimé."
        ], 201);
    }
}
