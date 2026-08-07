<?php

namespace App\Http\Controllers\Api;

use App\Actions\Report\SubmitReportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitReportRequest;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Report::class);

        $reports = Report::with('notes')
            ->where('reporter_id', $request->user()->id)
            ->latest()
            ->get();

        return $this->success(ReportResource::collection($reports));
    }

    public function store(SubmitReportRequest $request, SubmitReportAction $action)
    {
        $report = $action->execute(
            $request->user(),
            $request->validated(),
            $request->file('evidence', [])
        );

        return $this->success(new ReportResource($report), 'Report submitted.', 201);
    }

    public function show(Request $request, Report $report)
    {
        $this->authorize('view', $report);

        return $this->success(new ReportResource($report->load('notes')));
    }
}