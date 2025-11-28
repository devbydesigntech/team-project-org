<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'description',
    ];

    /**
     * Get the organization that owns the project
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the teams working on this project
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'project_team')
            ->withTimestamps();
    }

    /**
     * Get all reviews for this project
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get all advisory assignments for this project
     */
    public function advisoryAssignments(): HasMany
    {
        return $this->hasMany(AdvisoryAssignment::class);
    }

    /**
     * Get all advisors (users) assigned to this project
     */
    public function advisors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'advisory_assignments')
            ->withPivot('starts_at', 'ends_at')
            ->withTimestamps();
    }
}
