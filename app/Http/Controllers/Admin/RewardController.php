<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Reward;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RewardController extends Controller
{
    public function index(Event $event): JsonResponse
    {
        $rewards = $event->rewards()
            ->orderBy('drop_rate', 'desc')
            ->get();

        return response()->json([
            'rewards' => $rewards,
            'total_drop_rate' => $event->getTotalDropRate(),
        ]);
    }

    public function store(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'drop_rate' => ['required', 'numeric', 'min:0.01', 'max:100'],
        ]);

        $currentTotal = $event->getTotalDropRate();
        $newTotal = $currentTotal + $validated['drop_rate'];

        if ($newTotal > 100.00) {
            throw ValidationException::withMessages([
                'drop_rate' => "Adding this reward would exceed 100% total drop rate. Current: {$currentTotal}%, Max allowed: " . (100 - $currentTotal) . "%",
            ]);
        }

        $reward = $event->rewards()->create($validated);

        return response()->json([
            'message' => 'Reward created successfully',
            'reward' => $reward,
            'total_drop_rate' => $event->fresh()->getTotalDropRate(),
        ], 201);
    }

    public function show(Event $event, Reward $reward): JsonResponse
    {
        if ($reward->event_id !== $event->id) {
            return response()->json(['message' => 'Reward not found in this event'], 404);
        }

        return response()->json([
            'reward' => $reward,
        ]);
    }

    public function update(Request $request, Event $event, Reward $reward): JsonResponse
    {
        if ($reward->event_id !== $event->id) {
            return response()->json(['message' => 'Reward not found in this event'], 404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'drop_rate' => ['sometimes', 'numeric', 'min:0.01', 'max:100'],
        ]);

        if (isset($validated['drop_rate'])) {
            $currentTotal = $event->getTotalDropRate() - $reward->drop_rate;
            $newTotal = $currentTotal + $validated['drop_rate'];

            if ($newTotal > 100.00) {
                throw ValidationException::withMessages([
                    'drop_rate' => "This update would exceed 100% total drop rate. Max allowed: " . (100 - $currentTotal) . "%",
                ]);
            }
        }

        $reward->update($validated);

        return response()->json([
            'message' => 'Reward updated successfully',
            'reward' => $reward->fresh(),
            'total_drop_rate' => $event->fresh()->getTotalDropRate(),
        ]);
    }

    public function destroy(Event $event, Reward $reward): JsonResponse
    {
        if ($reward->event_id !== $event->id) {
            return response()->json(['message' => 'Reward not found in this event'], 404);
        }

        $reward->delete();

        return response()->json([
            'message' => 'Reward deleted successfully',
            'total_drop_rate' => $event->fresh()->getTotalDropRate(),
        ]);
    }

    public function validateDropRate(Event $event): JsonResponse
    {
        $totalDropRate = $event->getTotalDropRate();
        $isValid = abs($totalDropRate - 100) < 0.01;

        return response()->json([
            'total_drop_rate' => $totalDropRate,
            'is_valid' => $isValid,
            'message' => $isValid
                ? 'Drop rates are valid (total = 100%)'
                : "Drop rates are invalid. Current total: {$totalDropRate}%. Must equal 100%.",
        ]);
    }
}
