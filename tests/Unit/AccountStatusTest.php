<?php

namespace Tests\Unit;

use App\Enums\AccountStatus;
use PHPUnit\Framework\TestCase;

class AccountStatusTest extends TestCase
{
    public function test_there_are_exactly_three_account_statuses(): void
    {
        $this->assertCount(3, AccountStatus::cases());
    }

    public function test_account_status_values_match_spec(): void
    {
        $this->assertSame('active', AccountStatus::Active->value);
        $this->assertSame('suspended', AccountStatus::Suspended->value);
        $this->assertSame('deactivated', AccountStatus::Deactivated->value);
    }
}