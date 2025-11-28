<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReviewPolicy
{
    /**
     * Determine whether the user can view any models.
     * All users can list reviews (filtered in controller based on role)
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * Complex visibility rules based on user role
     */
    public function view(User $user, Review $review): bool
    {
        // Executives can see all reviews
        if ($user->isExecutive()) {
            return true;
        }

        // Users can see reviews they wrote
        if ($review->reviewer_id === $user->id) {
            return true;
        }

        // Users can see reviews about themselves
        if ($review->reviewee_user_id === $user->id) {
            return true;
        }

        // Check if user is an advisor on the project
        $isAdvisorOnProject = $user->advisoryProjects()
            ->where('projects.id', $review->project_id)
            ->exists();
        
        if ($isAdvisorOnProject) {
            return true;
        }

        // Managers can see reviews of their team members
        if ($user->isManager()) {
            // Get all teams the user manages
            $managedTeams = \App\Models\Team::where('manager_id', $user->id)->get();
            
            foreach ($managedTeams as $team) {
                // Check if review is about a team member
                if ($review->reviewee_user_id) {
                    $isTeamMember = $team->members()
                        ->where('users.id', $review->reviewee_user_id)
                        ->exists();
                    
                    if ($isTeamMember) {
                        return true;
                    }
                }
                
                // Check if review is about a team's project
                $isTeamProject = $team->projects()
                    ->where('projects.id', $review->project_id)
                    ->exists();
                
                if ($isTeamProject) {
                    return true;
                }
            }
        }

        // Associates can see reviews of their team's projects
        if ($user->isAssociate()) {
            // Get all teams the user is a member of
            $userTeams = $user->teams;
            
            foreach ($userTeams as $team) {
                $isTeamProject = $team->projects()
                    ->where('projects.id', $review->project_id)
                    ->exists();
                
                if ($isTeamProject) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     * Any user can create reviews
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     * Users can only update their own reviews
     */
    public function update(User $user, Review $review): bool
    {
        return $review->reviewer_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     * Users can delete their own reviews
     * Executives can delete any review
     */
    public function delete(User $user, Review $review): bool
    {
        return $review->reviewer_id === $user->id || $user->isExecutive();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Review $review): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Review $review): bool
    {
        return false;
    }
}
