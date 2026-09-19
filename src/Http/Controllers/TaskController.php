<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Http\Requests\TaskRequest;
use DsApps\LaravelCrm\Http\Resources\TaskResource;
use DsApps\LaravelCrm\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TaskController extends Controller
{
    public function index(Request $request): mixed
    {
        $query = Task::query();
        if ($status = $request->string('status')->toString()) $query->where('status', $status);
        if ($scope = $request->string('scope')->toString()) {
            if ($scope === 'overdue') $query->pending()->whereNotNull('due_at')->where('due_at', '<', now());
            if ($scope === 'today') $query->pending()->whereBetween('due_at', [today(), today()->endOfDay()]);
            if ($scope === 'upcoming') $query->pending()->where('due_at', '>=', now());
        }
        return TaskResource::collection($query->orderByRaw('due_at IS NULL')->orderBy('due_at')->paginate(min($request->integer('per_page', 25), 100)));
    }

    public function store(TaskRequest $request): TaskResource { return new TaskResource(Task::create($request->validated())); }
    public function show(Task $task): TaskResource { return new TaskResource($task); }

    public function update(TaskRequest $request, Task $task): TaskResource
    {
        $data = $request->validated();
        if (($data['status'] ?? $task->status) === 'completed') $data['completed_at'] = now();
        $task->update($data);
        return new TaskResource($task->refresh());
    }

    public function complete(Task $task): TaskResource
    {
        $task->update(['status' => 'completed', 'completed_at' => now()]);
        return new TaskResource($task->refresh());
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->update(['status' => 'cancelled']);
        return response()->json(null, 204);
    }
}
