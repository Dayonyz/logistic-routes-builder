<?php

namespace Tests\Unit\Services\Schedule;

use PHPUnit\Framework\TestCase;
use Src\Enums\RouteTypesEnum;
use Src\Services\Schedule\DailyRouteTypesGenerator;

class DailyRouteTypesGeneratorTest extends TestCase
{
    public function testGeneratesCorrectCount(): void
    {
        $generator = new DailyRouteTypesGenerator();

        $days = 30;
        $result = $generator->generateSchedule($days, true);

        $this->assertCount($days, $result);
    }

    public function testAllItemsAreRouteTypesEnum(): void
    {
        $generator = new DailyRouteTypesGenerator();

        $result = $generator->generateSchedule(20, true);

        foreach ($result as $type) {
            $this->assertInstanceOf(RouteTypesEnum::class, $type);
        }
    }

    public function testMinimumCountsAreSatisfied(): void
    {
        $generator = new DailyRouteTypesGenerator();

        $days = 60;
        $result = $generator->generateSchedule($days, true);

        $counts = [
            RouteTypesEnum::BLY->value => 0,
            RouteTypesEnum::LOZ->value => 0,
        ];

        foreach ($result as $type) {
            if (isset($counts[$type->value])) {
                $counts[$type->value]++;
            }
        }

        $blyMin = (int) round(
            (RouteTypesEnum::BLY->getOdds() / RouteTypesEnum::getOddsSum()) * $days * 0.88
        );

        $lozMin = (int) round(
            (RouteTypesEnum::LOZ->getOdds() / RouteTypesEnum::getOddsSum()) * $days * 0.88
        );

        $this->assertGreaterThanOrEqual($blyMin, $counts[RouteTypesEnum::BLY->value]);
        $this->assertGreaterThanOrEqual($lozMin, $counts[RouteTypesEnum::LOZ->value]);
    }

    public function testSumBlyAndLozWithinBounds(): void
    {
        $generator = new DailyRouteTypesGenerator();

        $days = 50;
        $result = $generator->generateSchedule($days, true);

        $sum = 0;

        foreach ($result as $type) {
            if (in_array($type, [RouteTypesEnum::BLY, RouteTypesEnum::LOZ], true)) {
                $sum++;
            }
        }

        $expected = (
                RouteTypesEnum::BLY->getOdds() +
                RouteTypesEnum::LOZ->getOdds()
            ) / RouteTypesEnum::getOddsSum() * $days;

        $min = (int) round($expected * 0.88);
        $max = (int) round($expected * 1.12);

        $this->assertGreaterThanOrEqual($min, $sum);
        $this->assertLessThanOrEqual($max, $sum);
    }

    public function testNoConsecutiveLozAndBlyWhenRestrictionsEnabled(): void
    {
        $generator = new DailyRouteTypesGenerator();

        $result = $generator->generateSchedule(40, false);

        for ($i = 0; $i < count($result) - 1; $i++) {
            $current = $result[$i];
            $next = $result[$i + 1];

            $this->assertFalse(
                ($current === RouteTypesEnum::LOZ && $next === RouteTypesEnum::LOZ) ||
                ($current === RouteTypesEnum::BLY && $next === RouteTypesEnum::BLY) ||
                ($current === RouteTypesEnum::LOZ && $next === RouteTypesEnum::BLY) ||
                ($current === RouteTypesEnum::BLY && $next === RouteTypesEnum::LOZ),
                "Invalid repetition at position {$i}"
            );
        }
    }

    public function testIsRouteRepeatedLogic(): void
    {
        $generator = new class extends DailyRouteTypesGenerator {
            public function test(RouteTypesEnum $a, RouteTypesEnum $b): bool {
                return $this->isRouteRepeated($a, $b);
            }
        };

        $this->assertTrue($generator->test(RouteTypesEnum::LOZ, RouteTypesEnum::LOZ));
        $this->assertTrue($generator->test(RouteTypesEnum::BLY, RouteTypesEnum::BLY));
        $this->assertTrue($generator->test(RouteTypesEnum::LOZ, RouteTypesEnum::BLY));
        $this->assertTrue($generator->test(RouteTypesEnum::BLY, RouteTypesEnum::LOZ));

        $this->assertFalse($generator->test(RouteTypesEnum::VIL, RouteTypesEnum::VIL));
        $this->assertFalse($generator->test(RouteTypesEnum::VIL, RouteTypesEnum::LOZ));
        $this->assertFalse($generator->test(RouteTypesEnum::VIL, RouteTypesEnum::BLY));
    }
}
