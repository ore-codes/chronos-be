<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\UserPreference;
use App\Services\UserPreferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserPreferenceController extends Controller
{
    public function getPreferences(Request $request): JsonResponse
    {
        return response()->json(UserPreference::where('user_id', $request->user()->id)->first());
    }

    public function savePreferences(Request $request, UserPreferenceService $service): JsonResponse
    {
        $preferences = $service->savePreferences($request);
        return response()->json([
            'message' => 'Preferences saved successfully!',
            'preferences' => $preferences,
        ]);
    }

    public function getSourcesAndCategories(): JsonResponse
    {
        return response()->json([
            'sources' => Article::distinct('source')->pluck('source'),
            'categories' => Article::distinct('category')->pluck('category'),
        ]);
    }

    public function searchAuthors(Request $request, UserPreferenceService $service): JsonResponse
    {
        $authors = $service->searchAuthors($request);
        return response()->json($authors);
    }

}
