<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     * Filter reviews based on user role and visibility rules
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Executives see all reviews
        if ($user->isExecutive()) {
            $reviews = Review::with(['reviewer', 'reviewee', 'project'])->get();
            return ReviewResource::collection($reviews);
        }

        // For non-executives, filter reviews based on visibility
        $reviews = Review::with(['reviewer', 'reviewee', 'project'])
            ->get()
            ->filter(function ($review) use ($user) {
                return Gate::forUser($user)->allows('view', $review);
            });

        return ReviewResource::collection($reviews);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Review::class);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'reviewee_user_id' => 'nullable|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string',
        ]);

        $validated['reviewer_id'] = $request->user()->id;

        $review = Review::create($validated);
        $review->load(['reviewer', 'reviewee', 'project']);

        return new ReviewResource($review);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Review $review)
    {
        $this->authorize('view', $review);

        $review->load(['reviewer', 'reviewee', 'project']);

        return new ReviewResource($review);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review)
    {
        $this->authorize('update', $review);

        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'content' => 'sometimes|string',
        ]);

        $review->update($validated);
        $review->load(['reviewer', 'reviewee', 'project']);

        return new ReviewResource($review);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Review $review)
    {
        $this->authorize('delete', $review);

        $review->delete();

        return response()->json(null, 204);
    }
}
