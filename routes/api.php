<?php

use App\Http\Controllers\Api\AdvisoryAssignmentController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Organizations
    Route::apiResource('organizations', OrganizationController::class);
    
    // Roles
    Route::apiResource('roles', RoleController::class);
    
    // Users
    Route::apiResource('users', UserController::class);
    
    // Teams
    Route::apiResource('teams', TeamController::class);
    Route::post('teams/{team}/members', [TeamController::class, 'addMember']);
    Route::delete('teams/{team}/members/{userId}', [TeamController::class, 'removeMember']);
    
    // Projects
    Route::apiResource('projects', ProjectController::class);
    Route::post('projects/{project}/teams', [ProjectController::class, 'assignTeam']);
    Route::delete('projects/{project}/teams/{teamId}', [ProjectController::class, 'removeTeam']);
    
    // Advisory Assignments
    Route::apiResource('advisory-assignments', AdvisoryAssignmentController::class);
    
    // Reviews
    Route::apiResource('reviews', ReviewController::class);
});
