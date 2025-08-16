<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'avatar' => 'nullable|image',
            'telephone' => 'nullable|string|max:20',
            'cin' => 'nullable|string|unique:users',
            'taille' => 'nullable|numeric|min:0.5|max:3.0',
            'poids' => 'nullable|numeric|min:10|max:500',
            'objectif' => 'nullable|in:prise de masse,perte de poids,maintien,prise de force,endurance,remise en forme,sèche,souplesse,rééducation,tonification,préparation physique,performance',
            'sexe' => 'nullable|in:homme,femme',
            'date_naissance' => 'nullable|date|before:today',
        ]);
        
        $avatar = null;
        if ($request->hasFile('avatar')) {
            $avatarName = $request->file('avatar')->getClientOriginalName();
            $request->file('avatar')->storeAs('avatar', $avatarName, 'public');
            $avatar = $avatarName;
        }
        
        $userData = [
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'avatar' => $avatar,
        ];

        // Ajouter les champs optionnels s'ils sont présents
        $optionalFields = ['telephone', 'cin', 'taille', 'objectif', 'sexe', 'date_naissance'];
        foreach ($optionalFields as $field) {
            if (isset($validatedData[$field])) {
                $userData[$field] = $validatedData[$field];
            }
        }

        // Initialiser l'historique de poids si fourni (poids actuel)
        if (isset($validatedData['poids'])) {
            $userData['poids'] = [
                [
                    'date' => now()->toDateString(),
                    'valeur' => (float) $validatedData['poids'],
                ]
            ];
        }

        $user = User::create($userData)->assignRole('challenger');
        $token = $user->createToken('authToken')->plainTextToken;
        
        return response()->json([
            'token' => $token,
            'user' => $user,
            'role' => $user->roles[0]->name,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user,
            'role' => $user->roles[0]->name,
            ], 200);
        }
        return response()->json(['message' => 'Email ou mot de passe incorrect.'], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Vous vous êtes déconnécté.']);
    }

}
