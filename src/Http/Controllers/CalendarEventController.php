<?php

namespace DsApps\LaravelCrm\Http\Controllers;

use DsApps\LaravelCrm\Http\Requests\CalendarEventRequest;
use DsApps\LaravelCrm\Http\Resources\CalendarEventResource;
use DsApps\LaravelCrm\Models\CalendarEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CalendarEventController extends Controller
{
    public function index(Request $request): mixed
    {
        $query = CalendarEvent::query()->where('status', 'scheduled');
        if ($request->filled('from')) $query->where('end_at', '>=', $request->date('from')->utc());
        if ($request->filled('to')) $query->where('start_at', '<=', $request->date('to')->utc());
        return CalendarEventResource::collection($query->orderBy('start_at')->paginate(min($request->integer('per_page', 50), 100)));
    }

    public function store(CalendarEventRequest $request): CalendarEventResource { return new CalendarEventResource(CalendarEvent::create($request->validated())); }
    public function show(CalendarEvent $calendarEvent): CalendarEventResource { return new CalendarEventResource($calendarEvent); }
    public function update(CalendarEventRequest $request, CalendarEvent $calendarEvent): CalendarEventResource
    {
        $calendarEvent->update($request->validated());
        return new CalendarEventResource($calendarEvent->refresh());
    }
    public function destroy(CalendarEvent $calendarEvent): JsonResponse
    {
        $calendarEvent->update(['status' => 'cancelled']);
        return response()->json(null, 204);
    }
}
