<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceSearchLog;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ServiceSearchLogController extends Controller
{
    use ApiResponses;

    /** Fire-and-forget: logs one real search submission. Anonymous, no auth required. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'term' => ['required', 'string', 'max:100'],
        ]);

        ServiceSearchLog::create(['term' => trim($validated['term'])]);

        return $this->success(null, 'Logged.', 201);
    }

    /** Top N terms by actual search frequency — true "most searched", not supply frequency. */
    public function popular(Request $request)
    {
        $limit = min((int) $request->query('limit', 4), 10);

        $popular = ServiceSearchLog::query()
            ->selectRaw('term, COUNT(*) as searches')
            ->groupBy('term')
            ->orderByDesc('searches')
            ->limit($limit)
            ->pluck('term');

        return $this->success($popular);
    }
}