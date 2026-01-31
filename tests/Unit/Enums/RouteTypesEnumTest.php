<?php

namespace Tests\Unit\Enums;

use Exception;
use PHPUnit\Framework\TestCase;
use Src\Enums\DestinationsEnum;
use Src\Enums\RouteTypesEnum;

class RouteTypesEnumTest extends TestCase
{
    public function testTitles(): void
    {
        $this->assertSame(
            'Route type - VILLAGES',
            RouteTypesEnum::VIL->title()
        );

        $this->assertSame(
            'Route type - LOZOVA',
            RouteTypesEnum::LOZ->title()
        );

        $this->assertSame(
            'Route type - BLYZNIUKY',
            RouteTypesEnum::BLY->title()
        );
    }

    public function testGetOdds(): void
    {
        $this->assertSame(34, RouteTypesEnum::VIL->getOdds());
        $this->assertSame(9, RouteTypesEnum::LOZ->getOdds());
        $this->assertSame(9, RouteTypesEnum::BLY->getOdds());
    }

    public function testOddsSum(): void
    {
        $this->assertSame(
            34 + 9 + 9,
            RouteTypesEnum::getOddsSum()
        );
    }

    public function testGetDestinationsForVil(): void
    {
        $destinations = RouteTypesEnum::VIL->getDestinations();

        $this->assertNotContains(DestinationsEnum::LOZ, $destinations);
        $this->assertNotContains(DestinationsEnum::BLY, $destinations);

        foreach ($destinations as $destination) {
            $this->assertInstanceOf(DestinationsEnum::class, $destination);
        }
    }

    public function testGetDestinationsForLoz(): void
    {
        $destinations = RouteTypesEnum::LOZ->getDestinations();

        $this->assertNotContains(DestinationsEnum::BLY, $destinations);
        $this->assertContains(DestinationsEnum::LOZ, $destinations);
    }

    public function testGetDestinationsForBly(): void
    {
        $destinations = RouteTypesEnum::BLY->getDestinations();

        $this->assertNotContains(DestinationsEnum::LOZ, $destinations);
        $this->assertContains(DestinationsEnum::BLY, $destinations);
    }

    public function testDestinationsCountMatchesActual(): void
    {
        foreach (RouteTypesEnum::cases() as $type) {
            $this->assertSame(
                count($type->getDestinations()),
                $type->getDestinationsCount()
            );
        }
    }

    /**
     * @throws Exception
     */
    public function testRandomTypeAlwaysReturnsEnum(): void
    {
        for ($i = 0; $i < 200; $i++) {
            $type = RouteTypesEnum::getRandomType();

            $this->assertInstanceOf(RouteTypesEnum::class, $type);
        }
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