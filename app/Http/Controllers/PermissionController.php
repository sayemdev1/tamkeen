<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermissionRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Store;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
 
    public function index()
    {
        return response()->json(Permission::select('id', 'name', 'description')->get());
    }

    public function store(StorePermissionRequest $request)
    {
        // Validate and retrieve data
        $validatedData = $request->validated();
    

        return response()->json(Permission::create($validatedData), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Permission  $permission
     * @return \Illuminate\Http\Response
     */
    public function show(Permission $permission)
    {
        return response()->json($permission);
    }


    public function update(StorePermissionRequest $request, Permission $permission)
    {
        // Validate and retrieve data
        $validatedData = $request->validated();
        $permission->update($validatedData);
        return response()->json(['message' => 'Permission updated successfully', 'permission' => $permission], 200);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return response()->json(['message' => 'Permission deleted successfully']);
    }


    public function storeRolePermission(Role $role, Request $request)
    {

      $permissionIds =  $request->input('ids');

      $role->permissions()->sync($permissionIds);
      return response()->json(['message' => 'Permissions assigned successfully'], 200);

    }


    public function rolePermissions(Role $role)
    {
        return response()->json(['permissions' => $role->with('permissions')->get()]);
    }

    public function userPermissions()
    {

        $user = auth()->user();
        return response()->json(['permissions of user' => $user->role->permissions]);
    }
}
