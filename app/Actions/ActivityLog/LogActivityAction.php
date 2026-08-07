<?php

namespace App\Actions\ActivityLog;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class LogActivityAction
{
    /**
     * Record an activity log entry.
     *
     * @param  User|null  $causer  Who performed the action. Null for system-triggered events.
     * @param  Model|null  $subject  What the action was done to.
     * @param  string  $action  Machine-readable key, e.g. "application.approved".
     * @param  string  $description  Human-readable summary shown in the UI.
     * @param  array  $metadata  Extra context (reasons, old/new values, amounts, etc.).
     */
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