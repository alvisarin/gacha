<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(): JsonResponse
    {
        $events = Event::with('rewards')
            ->withCount('gachaLogs')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($events);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $event = Event::create($validated);

        return response()->json([
            'message' => 'Event created successfully',
            'event' => $event->load('rewards'),
        ], 201);
    }

    public function show(Event $event): JsonResponse
    {
        return response()->json([
            'event' => $event->load('rewards'),
            'total_drop_rate' => $event->getTotalDropRate(),
        ]);
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $event->update($validated);

        return response()->json([
            'message' => 'Event updated successfully',
            'event' => $event->fresh()->load('rewards'),
        ]);
    }

    public function destroy(Event $event): JsonResponse
    {
        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully',
        ]);
    }

    public function activeEvents(): JsonResponse
    {
        $events = Event::active()
            ->with('rewards')
            ->get()
            ->filter(function ($event) {
                return abs($event->getTotalDropRate() - 100) < 0.01;
            })
            ->values();

        return response()->json([
            'events' => $events,
        ]);
    }
}
