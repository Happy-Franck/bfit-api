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
            'avatar' => 'nullable|image'
        ]);
        $avatar = null;
        if ($request->hasFile('avatar')) {
            $avatarName = $request->file('avatar')->store('avatars', 'public');
            $avatar = $avatarName;
        }
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'avatar' => $avatar,
        ])->assignRole('challenger');
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
