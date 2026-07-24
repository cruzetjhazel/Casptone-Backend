<?php

namespace App\Http\Controllers\Api;

use App\Actions\Photographer\ReapplyPhotographerApplicationAction;
use App\Actions\Photographer\SubmitPhotographerApplicationAction;
use App\Actions\Photographer\UpdatePhotographerApplicationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePhotographerApplicationRequest;
use App\Http\Resources\PhotographerApplicationResource;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PhotographerApplicationController extends Controller
{
    use ApiResponses;

    protected function applicationFor(Request $request)
    {
        $user = $request->user();

        if (! $user->isPhotographer()) {
            abort(403, 'Only Photographer accounts can access their application.');
        }

        $application = $user->photographerApplication;

        if (! $application) {
            throw new NotFoundHttpException('No photographer application found for this account.');
        }

        return $application;
    }

    public function show(Request $request)
    {
        $application = $this->applicationFor($request);
        $this->authorize('view', $application);

        return $this->success(new PhotographerApplicationResource($application));
    }

    public function update(UpdatePhotographerApplicationRequest $request, UpdatePhotographerApplicationAction $action)
    {
        $application = $this->applicationFor($request);
        $this->authorize('update', $application);

        $application = $action->execute($application, $request->validated(), [
            'government_id' => $request->file('government_id'),
            'selfie_with_id' => $request->file('selfie_with_id'),
            'business_permit' => $request->file('business_permit'),
            'additional_documents' => $request->file('additional_documents', []),
        ]);

        return $this->success(new PhotographerApplicationResource($application), 'Application updated.');
    }

    public function submit(Request $request, SubmitPhotographerApplicationAction $action)
    {
        $application = $this->applicationFor($request);
        $this->authorize('submit', $application);

        $application = $action->execute($application);

        return $this->success(new PhotographerApplicationResource($application), 'Application submitted for review.');
    }

    public function reapply(Request $request, ReapplyPhotographerApplicationAction $action)
    {
        $application = $this->applicationFor($request);
        $this->authorize('reapply', $application);

        $application = $action->execute($application);

        return $this->success(new PhotographerApplicationResource($application), 'You may now update and resubmit your application.');
    }
}