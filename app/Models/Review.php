<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    /** @use HasFactory<\Database\Factories\ReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'reviewer_id',
        'project_id',
        'reviewee_user_id',
        'rating',
        'content',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * The reviewer (user who wrote the review)
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * The reviewee (user being reviewed, optional)
     */
    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewee_user_id');
    }

    /**
     * The project being reviewed
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get visible reviewer name based on user role
     * Only executives can see the actual reviewer name
     */
    public function visibleReviewerName(?User $viewer = null): string
    {
        if (!$viewer) {
            return 'Anonymous';
        }

        // Executives can see reviewer identity
        if ($viewer->isExecutive()) {
            return $this->reviewer->name;
        }

        // Everyone else sees anonymous
        return 'Anonymous';
    }

    /**
     * Check if review is about a user (reviewee)
     */
    public function isUserReview(): bool
    {
        return !is_null($this->reviewee_user_id);
    }

    /**
     * Check if review is about a project only
     */
    public function isProjectReview(): bool
    {
        return is_null($this->reviewee_user_id);
    }
}
