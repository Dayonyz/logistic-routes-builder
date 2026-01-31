<?php

namespace Tests\Unit\Enums;

use PHPUnit\Framework\TestCase;
use Src\Enums\IntervalUnitsEnum;
use Exception;

class IntervalUnitsEnumTest extends TestCase
{
    public function testEnumValues(): void
    {
        $this->assertSame('M', IntervalUnitsEnum::UNIT->value);
        $this->assertSame('PT', IntervalUnitsEnum::PREFIX->value);
        $this->assertSame('5', IntervalUnitsEnum::MINUTE_STEP->value);
    }

    /**
     * @throws Exception
     */
    public function testToIntOnlyWorksForMinuteStep(): void
    {
        $this->assertSame(5, IntervalUnitsEnum::MINUTE_STEP->toInt());

        foreach ([IntervalUnitsEnum::UNIT, IntervalUnitsEnum::PREFIX] as $case) {
            try {
                $case->toInt();
                $this->fail("Exception expected for {$case->name}");
            } catch (Exception $e) {
                $this->assertStringContainsString("'$case->name' can not be converted to int", $e->getMessage());
            }
        }
    }
}
