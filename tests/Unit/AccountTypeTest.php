<?php

namespace Tests\Unit;

use App\Enums\AccountType;
use PHPUnit\Framework\TestCase;

class AccountTypeTest extends TestCase
{
    public function test_there_are_exactly_three_account_types(): void
    {
        $this->assertCount(3, AccountType::cases());
    }

    public function test_account_type_values_match_spec(): void
    {
        $this->assertSame('client', AccountType::Client->value);
        $this->assertSame('photographer', AccountType::Photographer->value);
        $this->assertSame('administrator', AccountType::Administrator->value);
    }
}