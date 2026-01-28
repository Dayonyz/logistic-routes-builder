<?php

namespace Tests\Unit\Enums;

use Exception;
use PHPUnit\Framework\TestCase;
use Src\Enums\RouteTypesEnum;

class RouteTypesEnumTest extends TestCase
{
    public function testOddsSum(): void
    {
        $expected = 0;

        foreach (RouteTypesEnum::cases() as $case) {
            $expected += $case->getOdds();
        }

        $this->assertSame($expected, RouteTypesEnum::getOddsSum());
    }

    public function testDestinationsCountIsPositive(): void
    {
        foreach (RouteTypesEnum::cases() as $case) {
            $this->assertGreaterThan(
                0,
                $case->getDestinationsCount(),
                "{$case->name} has invalid destinations count"
            );
        }
    }

    /**
     * @throws Exception
     */
    public function testGetRandomTypeReturnsEnum(): void
    {
        $type = RouteTypesEnum::getRandomType();

        $this->assertInstanceOf(RouteTypesEnum::class, $type);
    }

    /**
     * @throws Exception
     */
    public function testRandomTypeDistributionIsRoughlyCorrect(): void
    {
        $iterations = 10000;
        $counts = [];

        foreach (RouteTypesEnum::cases() as $case) {
            $counts[$case->value] = 0;
        }

        for ($i = 0; $i < $iterations; $i++) {
            $type = RouteTypesEnum::getRandomType();
            $counts[$type->value]++;
        }

        foreach (RouteTypesEnum::cases() as $case) {
            $expectedRatio = $case->getOdds() / RouteTypesEnum::getOddsSum();
            $actualRatio   = $counts[$case->value] / $iterations;

            $this->assertEqualsWithDelta(
                $expectedRatio,
                $actualRatio,
                0.02,
                "{$case->name} probability out of range"
            );
        }
    }
}