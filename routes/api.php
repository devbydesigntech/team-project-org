<?php

use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
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
});
