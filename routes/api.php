<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::patch('/change-password', [AuthController::class, 'changePassword']);
        Route::post('edit-user', [AuthController::class, 'editUser']);
        Route::post('/change-profile-picture', [AuthController::class, 'changeProfilePicture']);
    });
});


Route::post('/notes/pin_note/{id}/pin', [NoteController::class, 'pinNote']);
Route::post('/notes/unpin_note/{id}/unpin', [NoteController::class, 'unpinNote']);
Route::post('notes/archive_note/{id}/archive', [NoteController::class, 'archiveNote']);
Route::post('notes/publish_note/{id}/publish', [NoteController::class, 'publishNote']);
Route::apiResource('notes', NoteController::class);

Route::get('notes/stats/status', [NoteController::class, 'statsByStatus']);

Route::patch('notes/actions/archive-old-drafts', [NoteController::class, 'archiveOldDrafts']);

Route::get('users/{user}/notes', [NoteController::class, 'userNotesWithCategories']);

Route::get('notes-actions/search', [NoteController::class, 'search']);

Route::get('notes/actions/get_notes_by_category', [NoteController::class, 'getNotesByCategory']);

Route::get('notes/actions/get_pinned_notes', [NoteController::class, 'getPinnedNotes']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);

    Route::middleware('admin')->group(function () {
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
    });
});

Route::get('users/get_premium_users', [UserController::class, 'fetchPremiumUsers']);
Route::apiResource('users', UserController::class);
Route::apiResource('notes.tasks', TaskController::class)->scoped();
