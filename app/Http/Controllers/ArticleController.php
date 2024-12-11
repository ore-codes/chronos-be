<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ArticleController extends Controller
{
    public function index(Request $request, ArticleService $service): JsonResponse
    {
        return response()->json($service->getAllArticles($request));
    }

}
