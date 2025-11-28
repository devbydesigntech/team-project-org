<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdvisoryAssignmentResource;
use App\Models\AdvisoryAssignment;
use Illuminate\Http\Request;

class AdvisoryAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assignments = AdvisoryAssignment::with(['advisor', 'project'])->get();
        return AdvisoryAssignmentResource::collection($assignments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', AdvisoryAssignment::class);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $assignment = AdvisoryAssignment::create($validated);

        return new AdvisoryAssignmentResource($assignment->load(['advisor', 'project']));
    }

    /**
     * Display the specified resource.
     */
    public function show(AdvisoryAssignment $advisoryAssignment)
    {
        return new AdvisoryAssignmentResource($advisoryAssignment->load(['advisor', 'project']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AdvisoryAssignment $advisoryAssignment)
    {
        $this->authorize('update', $advisoryAssignment);

        $validated = $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'project_id' => 'sometimes|required|exists:projects,id',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $advisoryAssignment->update($validated);

        return new AdvisoryAssignmentResource($advisoryAssignment->load(['advisor', 'project']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdvisoryAssignment $advisoryAssignment)
    {
        $this->authorize('delete', $advisoryAssignment);

        $advisoryAssignment->delete();

        return response()->noContent();
    }
}
