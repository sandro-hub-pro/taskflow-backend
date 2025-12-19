<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Email verification (signature validated in controller)
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->name('verification.verify');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail'])
        ->middleware('throttle:6,1');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Profile
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::post('/profile/picture', [UserController::class, 'updateProfilePicture']);
    Route::put('/profile/password', [UserController::class, 'changePassword']);

    // Users (Admin only)
    Route::middleware('verified')->group(function () {
        Route::apiResource('users', UserController::class);
    });

    // Projects
    Route::apiResource('projects', ProjectController::class);
    Route::put('/projects/{project}/members', [ProjectController::class, 'updateMembers']);
    Route::get('/projects/{project}/statistics', [ProjectController::class, 'statistics']);

    // Tasks
    Route::get('/my-tasks', [TaskController::class, 'myTasks']);
    Route::apiResource('projects.tasks', TaskController::class);
    Route::put('/projects/{project}/tasks/{task}/assign', [TaskController::class, 'assignUsers']);
    Route::post('/projects/{project}/tasks/{task}/comments', [TaskController::class, 'addComment']);
    Route::delete('/projects/{project}/tasks/{task}/comments/{comment}', [TaskController::class, 'deleteComment']);
});
