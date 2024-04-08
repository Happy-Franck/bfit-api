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
        $users = User::all()->load('roles');
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
        if($user->hasRole('coach')){
            $seances = $user->coachSeances;
        }
        if($user->hasRole('challenger')){
            $seances = $user->challengerSeances;
        }
        if($user->hasRole('administrateur')) {
            $seances = $user->seances;
        }
        return response()->json([
            'user' => $user,
            'roles' => $user->roles,
            'permissions' => $user->permissions,
            'challengers' => $user->challengers,
            'seances' => $seances,
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
        return response()->json([
            'allChallengers' => User::role('challenger')->get(),
            'myChallengers' => Auth::user()->challengers
        ]);
    }
    public function showChallenger(User $user){
        return response()->json([
            'challenger' => $user->load('challengerSeances.trainings'),
        ]);
    }

    public function myCoachs(User $user)
    {
        return response()->json([
            'coachs' => $user->coachs,
        ], 200);
    }
}
