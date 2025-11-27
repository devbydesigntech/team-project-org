<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'role_id',
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the organization that owns the user.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the teams that the user belongs to.
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members')
            ->withPivot('team_role')
            ->withTimestamps();
    }

    /**
     * Get reviews written by this user.
     */
    public function reviewsWritten()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    /**
     * Get reviews about this user.
     */
    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'reviewee_user_id');
    }

    /**
     * Get advisory assignments for this user.
     */
    public function advisoryAssignments()
    {
        return $this->hasMany(AdvisoryAssignment::class);
    }

    /**
     * Get projects where this user is an advisor.
     */
    public function advisoryProjects()
    {
        return $this->belongsToMany(Project::class, 'advisory_assignments')
            ->withPivot('starts_at', 'ends_at')
            ->withTimestamps();
    }

    /**
     * Check if user is an executive.
     */
    public function isExecutive(): bool
    {
        return $this->role->name === 'executive';
    }

    /**
     * Check if user is a manager.
     */
    public function isManager(): bool
    {
        return $this->role->name === 'manager';
    }

    /**
     * Check if user is an associate.
     */
    public function isAssociate(): bool
    {
        return $this->role->name === 'associate';
    }
}
