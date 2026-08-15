<?php

namespace App\Actions\ActivityLog;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class LogActivityAction
{
    public function execute(
        ?User $causer,
        ?Model $subject,
        string $action,
        string $description,
        array $metadata = []
    ): ActivityLog {
        return ActivityLog::create([
            'causer_id' => $causer?->id,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}