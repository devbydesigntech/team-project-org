<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $teams = Team::with(['organization', 'manager', 'members'])->get();
        return TeamResource::collection($teams);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Team::class);

        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'manager_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
        ]);

        $team = Team::create($validated);
        $team->load(['organization', 'manager']);
        return new TeamResource($team);
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team)
    {
        $team->load(['organization', 'manager', 'members']);
        return new TeamResource($team);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Team $team)
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'organization_id' => 'sometimes|required|exists:organizations,id',
            'manager_id' => 'nullable|exists:users,id',
            'name' => 'sometimes|required|string|max:255',
        ]);

        $team->update($validated);
        $team->load(['organization', 'manager']);
        return new TeamResource($team);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);

        $team->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Add a member to the team.
     */
    public function addMember(Request $request, Team $team)
    {
        $this->authorize('addMember', $team);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'team_role' => 'nullable|string|max:255',
        ]);

        $team->members()->attach($validated['user_id'], [
            'team_role' => $validated['team_role'] ?? null,
        ]);

        $team->load(['members']);
        return new TeamResource($team);
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(Team $team, int $userId)
    {
        $this->authorize('removeMember', $team);

        $team->members()->detach($userId);
        return response()->json(['message' => 'Member removed successfully']);
    }
}
