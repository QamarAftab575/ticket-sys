<?php

namespace App\Http\Controllers;

use App\Services\GlobalSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function __construct(private GlobalSearchService $searchService)
    {
    }

    /**
     * Search tasks and projects.
     * GET /api/search
     *
     * Query Parameters:
     * - q: Search keyword (optional)
     * - page: Page number for task pagination (default: 1)
     * - limit: Number of tasks per page (default: 10)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:255',
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $searchQuery = $validated['q'] ?? null;
        $page = $validated['page'] ?? 1;
        $limit = $validated['limit'] ?? 10;

        $results = $this->searchService->search(
            auth()->user(),
            $searchQuery,
            $page,
            $limit
        );

        return response()->json($results);
    }
}
