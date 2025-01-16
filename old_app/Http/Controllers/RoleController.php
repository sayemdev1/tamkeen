<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
 
    public function index()
    {
       return response()->json(['roles' =>Role::select('id', 'name', 'description')->get()]);
    }

    public function store(RoleRequest $request)
    {
        return response()->json(['message'=>'Role created successfully' , 'role' => Role::create($request->validated())], 201);
    }

    public function show(Role $role)
    {
        return response()->json(['role' => $role]);
    }


    public function update(RoleRequest $request, Role $role)
    {
        $role->update($request->validated());
        return response()->json(['message' => 'Role updated successfully', 'role' => $role], 200);
    }

 
    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(['message' => 'Role deleted successfully']);
    }

   
}
