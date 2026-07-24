<?php

namespace Tests\Unit;

use App\Enums\PhotographerApplicationStatus;
use PHPUnit\Framework\TestCase;

class PhotographerApplicationStatusTest extends TestCase
{
    public function test_there_are_exactly_five_application_statuses(): void
    {
        $this->assertCount(5, PhotographerApplicationStatus::cases());
    }

    public function test_application_status_values_match_spec(): void
    {
        $this->assertSame('draft', PhotographerApplicationStatus::Draft->value);
        $this->assertSame('pending_review', PhotographerApplicationStatus::PendingReview->value);
        $this->assertSame('revision_requested', PhotographerApplicationStatus::RevisionRequested->value);
        $this->assertSame('approved', PhotographerApplicationStatus::Approved->value);
        $this->assertSame('rejected', PhotographerApplicationStatus::Rejected->value);
    }
}