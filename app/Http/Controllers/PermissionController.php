<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(){
        $permissions = Permission::all();
        return response()->json([
            'permissions' => $permissions,
        ], 201);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        permission::create([
            'name' => $request->name,
        ]);
        return response()->json([
            'message' => 'La permission a bien été créé.'
        ], 200);
    }

    public function update(Request $request, Permission $permission)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        $permission->name = $request['name'];
        $permission->save();
        return response()->json([
            'message' => "La permission a bien été modifié."
        ], 200);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return response()->json([
            'message' => "Le permission a bien été supprimé."
        ], 204);
    }

    public function assignrole(Request $request,Permission $permission){
        if($permission->hasRole($request->role)){
            return response()->json([
                'message' => 'Cette permission a déjà ce role.'
            ], 200);
        }
        $permission->assignRole($request->role);
        return response()->json([
            'message' => 'Les rôles pour cette permission a été mise à jour.'
        ], 200);
    }

    public function removerole(Permission $permission, Role $role){
        if($permission->hasRole($role)){
            $permission->removeRole($role);
            return response()->json([
                'message' => 'Les permissions pour ce rôle a été mise à jour.'
            ], 200);
        }
        return response()->json([
            'message' => 'Vous ne pouvez pas supprimer car ce rôle ne contient pas cette permission.'
        ], 200);
    }
}
