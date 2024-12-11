<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\UserPreferenceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/preferences', [UserPreferenceController::class, 'getPreferences']);
    Route::post('/preferences', [UserPreferenceController::class, 'savePreferences']);
    Route::get('/preference-options', [UserPreferenceController::class, 'getSourcesAndCategories']);
    Route::get('/authors/search', [UserPreferenceController::class, 'searchAuthors']);
});
