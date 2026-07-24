<?php

namespace App\Actions\Photographer;

use App\Models\PhotographerApplication;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class UpdatePhotographerApplicationAction
{
    public function execute(PhotographerApplication $application, array $data, array $files = []): PhotographerApplication
    {
        if (! $application->isEditable()) {
            throw ValidationException::withMessages([
                'status' => ['This application can no longer be edited in its current status.'],
            ]);
        }

        $fields = collect($data)->only([
            'business_name', 'location', 'years_active', 'team_size',
            'services', 'other_services', 'coverage_area', 'shooting_types',
            'price_min', 'price_max',
        ])->toArray();

        if (! empty($fields['shooting_types']) && in_array('hybrid', $fields['shooting_types'], true)) {
            $fields['shooting_types'] = array_values(array_unique(array_merge(
                $fields['shooting_types'],
                ['indoor', 'outdoor']
            )));
        }

        if (array_key_exists('government_id', $files) && $files['government_id'] instanceof UploadedFile) {
            $fields['government_id_path'] = $this->store($application, $files['government_id']);
        }

        if (array_key_exists('selfie_with_id', $files) && $files['selfie_with_id'] instanceof UploadedFile) {
            $fields['selfie_with_id_path'] = $this->store($application, $files['selfie_with_id']);
        }

        if (array_key_exists('business_permit', $files) && $files['business_permit'] instanceof UploadedFile) {
            $fields['business_permit_path'] = $this->store($application, $files['business_permit']);
        }

        if (! empty($files['additional_documents'])) {
            $paths = $application->additional_document_paths ?? [];
            foreach ($files['additional_documents'] as $file) {
                if ($file instanceof UploadedFile) {
                    $paths[] = $this->store($application, $file);
                }
            }
            $fields['additional_document_paths'] = $paths;
        }

        $application->fill($fields)->save();

        return $application->fresh();
    }

    protected function store(PhotographerApplication $application, UploadedFile $file): string
    {
        return $file->store("verification-documents/{$application->user_id}", 'local');
    }
}