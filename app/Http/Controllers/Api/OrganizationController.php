<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $organizations = Organization::with(['users', 'teams'])->get();
        return OrganizationResource::collection($organizations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Organization::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $organization = Organization::create($validated);
        return new OrganizationResource($organization);
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization)
    {
        $organization->load(['users', 'teams']);
        return new OrganizationResource($organization);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
        ]);

        $organization->update($validated);
        return new OrganizationResource($organization);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organization $organization)
    {
        $this->authorize('delete', $organization);

        $organization->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
