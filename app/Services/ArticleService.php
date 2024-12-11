<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Article;
use App\Models\UserPreference;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

final class ArticleService
{
    public function getAllArticles(Request $request): LengthAwarePaginator
    {
        $preferences = UserPreference::where('user_id', $request->user()->id)->first();

        return $request->filled('keyword')
            ? $this->searchArticles($request, $preferences)
            : $this->filterArticles($request, $preferences);
    }

    private function searchArticles(Request $request, ?UserPreference $preferences): LengthAwarePaginator
    {
        return Article::search($request->keyword)
            ->when($preferences, fn($query) => $this->applyPreferences($query, $preferences))
            ->when($request->filled('date'), fn($query) => $query->where('published_at', $request->date))
            ->when($request->filled('category'), fn($query) => $query->where('category', $request->category))
            ->when($request->filled('source'), fn($query) => $query->where('source', $request->source))
            ->paginate(9);
    }

    private function filterArticles(Request $request, ?UserPreference $preferences): LengthAwarePaginator
    {
        return Article::query()
            ->when($preferences, fn($query) => $this->applyPreferences($query, $preferences))
            ->when($request->filled('date'), fn($query) => $query->whereDate('published_at', $request->date))
            ->when($request->filled('category'), fn($query) => $query->where('category', $request->category))
            ->when($request->filled('source'), fn($query) => $query->where('source', $request->source))
            ->paginate(9);
    }

    private function applyPreferences($query, UserPreference $preferences): void
    {
        if ( ! empty($preferences->sources)) {
            $query->whereIn('source', $preferences->sources);
        }
        if ( ! empty($preferences->categories)) {
            $query->whereIn('category', $preferences->categories);
        }
        if ( ! empty($preferences->authors)) {
            $query->whereIn('author', $preferences->authors);
        }
    }
}
