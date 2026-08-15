<?php

namespace App\Http\Controllers\Api\Photographer;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityLogResource;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        return $this->success(
            ActivityLogResource::collection(
                $request->user()->activityLogs()->limit(200)->get()
            )
        );
    }
}