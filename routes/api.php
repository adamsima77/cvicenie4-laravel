<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
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

Route::prefix('notes')->group(function () {
    Route::post('/pin_note/{id}/pin', [NoteController::class, 'pinNote']);
    Route::post('/unpin_note/{id}/unpin', [NoteController::class, 'unpinNote']);
    Route::post('/archive_note/{id}/archive', [NoteController::class, 'archiveNote']);
    Route::post('/publish_note/{id}/publish', [NoteController::class, 'publishNote']);
    Route::get('/my-notes', [NoteController::class, 'myNotes'])->middleware('auth:sanctum');

    Route::apiResource('', NoteController::class);

});

Route::get('users/get_premium_users', [UserController::class, 'fetchPremiumUsers']);
Route::apiResource('users', UserController::class);
Route::apiResource('notes.tasks', TaskController::class)->scoped();

Route::get('/attachments/{attachment:public_id}/link', [AttachmentController::class, 'link']);




Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('comments', CommentController::class);
    Route::prefix('comments')->group(function () {
        Route::get('/task_index', [CommentController::class, 'taskIndex']);
        Route::get('/note_index', [CommentController::class, 'noteIndex']);
        Route::post('/task_store', [CommentController::class, 'taskStore']);
        Route::post('/note_store', [CommentController::class, 'noteStore']);

    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('notes/{note}')->group(function () {
        Route::get('attachments', [AttachmentController::class, 'index']);
        Route::post('attachments', [AttachmentController::class, 'store'])
            ->middleware('premium_only');
    });
    Route::get('attachments/{attachment:public_id}/link', [AttachmentController::class, 'link']);
    Route::delete('attachments/{attachment:public_id}', [AttachmentController::class, 'destroy']);
});
