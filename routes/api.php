<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;

Route::apiResource('notes', NoteController::class);

Route::get('notes/stats/status', [NoteController::class, 'statsByStatus']);

Route::patch('notes/actions/archive-old-drafts', [NoteController::class, 'archiveOldDrafts']);

Route::get('users/{user}/notes', [NoteController::class, 'userNotesWithCategories']);

Route::get('notes-actions/search', [NoteController::class, 'search']);

Route::get('notes/actions/get_notes_by_category', [NoteController::class, 'getNotesByCategory']);

Route::get('notes/actions/get_pinned_notes', [NoteController::class, 'getPinnedNotes']);

Route::apiResource('categories', CategoryController::class);
