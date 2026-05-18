<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetTasksRequest;
use App\Http\Requests\SaveViewPreferencesRequest;
use App\Services\MyTasksService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MyTasksController extends Controller
{
    public function __construct(private MyTasksService $myTasksService)
    {
    }

    /**
     * Display the My Tasks page.
     */
    public function index(): Response
    {
        return Inertia::render('MyTasks/Index');
    }

    /**
     * Get user's tasks with filters, sort, and grouping.
     */
    public function getTasks(GetTasksRequest $request): JsonResponse
    {
        $filters = $request->validated('filters') ? json_decode($request->validated('filters'), true) : [];
        $sort = $request->validated('sort') ? json_decode($request->validated('sort'), true) : [];
        $grouping = $request->validated('grouping');
        $page = $request->validated('page', 1);
        $perPage = $request->validated('per_page', 50);

        $tasks = $this->myTasksService->getUserTasks(
            auth()->user(),
            $filters,
            $sort,
            $grouping,
            $page,
            $perPage
        );

        return response()->json($tasks);
    }

    /**
     * Save view preferences for My Tasks.
     */
    public function saveViewPreferences(SaveViewPreferencesRequest $request): JsonResponse
    {
        $preference = $this->myTasksService->saveViewPreferences(
            auth()->user(),
            $request->validated()
        );

        return response()->json(['success' => true, 'preference' => $preference]);
    }

    /**
     * Get view preferences for My Tasks.
     */
    public function getViewPreferences(string $viewType): JsonResponse
    {
        $preference = $this->myTasksService->getViewPreferences(
            auth()->user(),
            $viewType
        );

        return response()->json($preference ?? []);
    }
}