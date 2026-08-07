<?php

namespace App\Http\Requests;

use App\Enums\AccountType;
use App\Enums\ReportRequestedAction;
use App\Enums\ReportSeverity;
use App\Enums\ReportTargetType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // A Client reports on a Studio/Professional; a Photographer reports on
        // a Client. Neither can target their own account type. Matches the
        // dynamic targetOptions split in ReportProblem.tsx.
        $isPhotographer = $this->user()->account_type === AccountType::Photographer;

        $allowedTargets = $isPhotographer
            ? [ReportTargetType::Client, ReportTargetType::Booking, ReportTargetType::Payment, ReportTargetType::Bug, ReportTargetType::Other]
            : [ReportTargetType::Studio, ReportTargetType::Booking, ReportTargetType::Payment, ReportTargetType::Bug, ReportTargetType::Other];

        return [
            'target_type' => ['required', Rule::enum(ReportTargetType::class), Rule::in($allowedTargets)],
            'reference_id' => ['nullable', 'string', 'max:100'],
            'reason' => ['required', 'string', 'max:255'],
            'severity' => ['required', Rule::enum(ReportSeverity::class)],
            'details' => ['required', 'string', 'max:2000'],
            'requested_action' => ['required', Rule::enum(ReportRequestedAction::class)],
            'evidence' => ['nullable', 'array', 'max:3'],
            'evidence.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }
}