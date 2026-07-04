<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\GachaLog;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GachaController extends Controller
{
    private const ROLL_COST = 10;

    public function roll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => ['required', 'exists:events,id'],
        ]);

        $event = Event::with('rewards')->findOrFail($validated['event_id']);

        if (!$event->is_active) {
            throw ValidationException::withMessages([
                'event_id' => 'This event is not currently active.',
            ]);
        }

        if ($event->rewards->isEmpty()) {
            throw ValidationException::withMessages([
                'event_id' => 'This event has no rewards configured.',
            ]);
        }

        $totalDropRate = $event->getTotalDropRate();
        if (abs($totalDropRate - 100) >= 0.01) {
            throw ValidationException::withMessages([
                'event_id' => 'This event has invalid reward configuration.',
            ]);
        }

        $result = DB::transaction(function () use ($request, $event) {
            $user = User::where('id', $request->user()->id)
                ->lockForUpdate()
                ->first();

            if ($user->coins < self::ROLL_COST) {
                throw ValidationException::withMessages([
                    'coins' => 'Insufficient coins. You need ' . self::ROLL_COST . ' coins to roll.',
                ]);
            }

            $user->coins -= self::ROLL_COST;
            $user->save();

            $reward = $this->selectReward($event->rewards);

            $log = GachaLog::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'reward_id' => $reward->id,
                'coins_spent' => self::ROLL_COST,
            ]);

            return [
                'user' => $user,
                'reward' => $reward,
                'log' => $log,
            ];
        });

        return response()->json([
            'message' => 'Congratulations!',
            'reward' => [
                'id' => $result['reward']->id,
                'name' => $result['reward']->name,
                'drop_rate' => $result['reward']->drop_rate,
            ],
            'remaining_coins' => $result['user']->coins,
            'roll_id' => $result['log']->id,
        ]);
    }

    private function selectReward($rewards): Reward
    {
        $random = mt_rand(1, 10000) / 100;
        $cumulative = 0;

        $sortedRewards = $rewards->sortBy('drop_rate');

        foreach ($sortedRewards as $reward) {
            $cumulative += (float) $reward->drop_rate;
            if ($random <= $cumulative) {
                return $reward;
            }
        }

        return $rewards->last();
    }

    public function history(Request $request): JsonResponse
    {
        $logs = GachaLog::with(['event:id,title', 'reward:id,name,drop_rate'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($logs);
    }

    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $totalRolls = GachaLog::where('user_id', $user->id)->count();
        $totalSpent = GachaLog::where('user_id', $user->id)->sum('coins_spent');

        $rewardBreakdown = GachaLog::where('user_id', $user->id)
            ->selectRaw('reward_id, COUNT(*) as count')
            ->with('reward:id,name,drop_rate')
            ->groupBy('reward_id')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'total_rolls' => $totalRolls,
            'total_coins_spent' => $totalSpent,
            'current_coins' => $user->coins,
            'reward_breakdown' => $rewardBreakdown,
        ]);
    }

    public function activeEvents(): JsonResponse
    {
        $events = Event::active()
            ->with('rewards:id,event_id,name,drop_rate')
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
