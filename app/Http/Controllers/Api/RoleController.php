<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();
        return RoleResource::collection($roles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Role::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
        ]);

        $role = Role::create($validated);
        return new RoleResource($role);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return new RoleResource($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $this->authorize('update', $role);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:roles,name,' . $role->id,
        ]);

        $role->update($validated);
        return new RoleResource($role);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);

        $role->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
