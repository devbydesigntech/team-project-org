<?php

namespace App\Policies;

use App\Models\AdvisoryAssignment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AdvisoryAssignmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All users can view advisory assignments
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AdvisoryAssignment $advisoryAssignment): bool
    {
        // All users can view an advisory assignment
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only executives can create advisory assignments
        return $user->isExecutive();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AdvisoryAssignment $advisoryAssignment): bool
    {
        // Only executives can update advisory assignments
        return $user->isExecutive();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AdvisoryAssignment $advisoryAssignment): bool
    {
        // Only executives can delete advisory assignments
        return $user->isExecutive();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, AdvisoryAssignment $advisoryAssignment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, AdvisoryAssignment $advisoryAssignment): bool
    {
        return false;
    }
}
