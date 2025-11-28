<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvisoryAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the user (advisor) for this assignment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the advisor (alias for user)
     */
    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the project for this advisory assignment
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Check if the advisory assignment is currently active
     */
    public function isActive(): bool
    {
        $now = now();
        
        $startsValid = is_null($this->starts_at) || $this->starts_at->lte($now);
        $endsValid = is_null($this->ends_at) || $this->ends_at->gte($now);
        
        return $startsValid && $endsValid;
    }
}
