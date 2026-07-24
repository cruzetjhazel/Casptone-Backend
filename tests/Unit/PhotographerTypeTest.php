<?php

namespace Tests\Unit;

use App\Enums\PhotographerType;
use PHPUnit\Framework\TestCase;

class PhotographerTypeTest extends TestCase
{
    public function test_there_are_exactly_two_photographer_types(): void
    {
        $this->assertCount(2, PhotographerType::cases());
    }

    public function test_photographer_type_values_match_spec(): void
    {
        $this->assertSame('freelancer', PhotographerType::Freelancer->value);
        $this->assertSame('studio', PhotographerType::Studio->value);
    }
}