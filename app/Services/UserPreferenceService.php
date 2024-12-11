<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Article;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

final class UserPreferenceService
{
    public function savePreferences(Request $request): UserPreference
    {
        $request->validate([
            'sources' => 'nullable|array',
            'categories' => 'nullable|array',
            'authors' => 'nullable|array',
        ]);

        return UserPreference::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'sources' => $request->sources,
                'categories' => $request->categories,
                'authors' => $request->authors,
            ],
        );
    }

    public function searchAuthors(Request $request): Collection
    {
        $search = $request->input('q');
        return Article::where('author', 'like', "%{$search}%")
            ->distinct('author')
            ->pluck('author')
            ->take(10);
    }

}
