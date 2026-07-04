<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GachaLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GachaLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = GachaLog::with(['user:id,name,email', 'event:id,title', 'reward:id,name,drop_rate'])
            ->orderBy('created_at', 'desc');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate($request->get('per_page', 20));

        return response()->json($logs);
    }

    public function stats(): JsonResponse
    {
        $totalRolls = GachaLog::count();
        $totalCoinsSpent = GachaLog::sum('coins_spent');
        $todayRolls = GachaLog::whereDate('created_at', today())->count();

        $topRewards = GachaLog::selectRaw('reward_id, COUNT(*) as count')
            ->with('reward:id,name,drop_rate')
            ->groupBy('reward_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return response()->json([
            'total_rolls' => $totalRolls,
            'total_coins_spent' => $totalCoinsSpent,
            'today_rolls' => $todayRolls,
            'top_rewards' => $topRewards,
        ]);
    }

    public function realtime(Request $request): JsonResponse
    {
        $lastId = $request->get('last_id', 0);

        $logs = GachaLog::with(['user:id,name', 'event:id,title', 'reward:id,name,drop_rate'])
            ->where('id', '>', $lastId)
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'logs' => $logs,
            'last_id' => $logs->first()?->id ?? $lastId,
        ]);
    }
}
