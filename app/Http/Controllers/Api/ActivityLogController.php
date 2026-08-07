<?php

namespace App\Http\Controllers\Api;

use App\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Models\ActivityLog;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    use ApiResponses;

    /**
     * GET /photographer/activity-logs and GET /client/activity-logs
     * Scoped to the authenticated user's own activity.
     */
    public function mine(Request $request)
    {
        $this->authorize('viewAny', ActivityLog::class);

        $logs = ActivityLog::with('causer')
            ->visibleTo($request->user())
            ->latest('created_at')
            ->paginate($request->integer('per_page', 20));

        return $this->success(ActivityLogResource::collection($logs));
    }

    /**
     * GET /admin/activity-logs
     * Full system log, optionally filtered by action or causer.
     */
    public function index(Request $request)
    {
        abort_unless($request->user()->account_type === AccountType::Administrator, 403);

        $query = ActivityLog::with('causer')->latest('created_at');

        if ($request->filled('action')) {
            $query->where('action', 'like', $request->string('action').'%');
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->integer('causer_id'));
        }

        $logs = $query->paginate($request->integer('per_page', 20));

        return $this->success(ActivityLogResource::collection($logs));
    }
}