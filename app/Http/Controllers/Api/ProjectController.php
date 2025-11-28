<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::with(['organization', 'teams'])->get();
        return ProjectResource::collection($projects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        $project = Project::create($validated);

        return new ProjectResource($project->load(['organization']));
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return new ProjectResource($project->load(['organization', 'teams']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'organization_id' => 'sometimes|required|exists:organizations,id',
        ]);

        $project->update($validated);

        return new ProjectResource($project->load(['organization', 'teams']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->noContent();
    }

    /**
     * Assign a team to the project.
     */
    public function assignTeam(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
        ]);

        // Attach the team if not already attached
        $project->teams()->syncWithoutDetaching([$validated['team_id']]);

        return new ProjectResource($project->load(['organization', 'teams']));
    }

    /**
     * Remove a team from the project.
     */
    public function removeTeam(Project $project, $teamId)
    {
        $this->authorize('update', $project);

        $project->teams()->detach($teamId);

        return new ProjectResource($project->load(['organization', 'teams']));
    }
}
