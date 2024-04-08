<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(){
        $roles = Role::all();
        return response()->json([
            'roles' => $roles,
        ], 201);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        Role::create([
            'name' => $request->name,
        ]);
        return response()->json([
            'message' => 'Le role a bien été créé.'
        ], 200);
    }

    public function update(Request $request, Role $role)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);
        $role->name = $request['name'];
        $role->save();
        return response()->json([
            'message' => "Le role a bien été modifié."
        ], 200);
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json([
            'message' => "Le role a bien été supprimé."
        ], 204);
    }

    public function permissiongive(Request $request, Role $role){
        if($role->hasPermissionTo($request->permission)){
            return response()->json([
                'message' => 'Ce rôle a déjà cette permission.'
            ], 200);
        }
        $role->givePermissionTo($request->permission);
        return response()->json([
            'message' => 'Les permissions pour ce rôle a été mise à jour.'
        ], 200);
    }

    public function permissionrevoke(Role $role, Permission $permission){
        if($role->hasPermissionTo($permission)){
            $role->revokePermissionTo($permission);
            return response()->json([
                'message' => 'Les permissions pour ce rôle a été mise à jour.'
            ], 200);
        }
        return response()->json([
            'message' => 'Vous ne pouvez pas supprimer car ce rôle ne contient pas cette permission.'
        ], 200);
    }
}
