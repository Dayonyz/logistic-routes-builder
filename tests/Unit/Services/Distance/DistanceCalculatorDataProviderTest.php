<?php

namespace Tests\Unit\Services\Distance;

use Generator;
use JsonException;
use PHPUnit\Framework\TestCase;
use Src\Enums\DestinationsEnum;
use Src\Services\Distance\DistanceCalculator;

class DistanceCalculatorDataProviderTest extends TestCase
{
    private DistanceCalculator $calculator;

    /**
     * @throws JsonException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new DistanceCalculator(__DIR__ . '/matrix.json');
    }

    private static function getDestinationsEnum(): array
    {
        return DestinationsEnum::cases();
    }

    /**
     * @dataProvider provideAllPairs
     */
    public function testDistanceForPairs(DestinationsEnum $from, DestinationsEnum $to): void
    {
        $distance = $this->calculator->getDistanceBetweenDestinations($from, $to);
        $distanceCell = $this->calculator->getDistanceMatrixCellBetweenDestinations($from, $to);

        $this->assertIsArray($distanceCell);
        $this->assertArrayHasKey('s', $distanceCell);
        $this->assertArrayHasKey('g', $distanceCell);
        $this->assertArrayHasKey('n', $distanceCell);
        $this->assertArrayHasKey('b', $distanceCell);

        $this->assertIsInt($distanceCell['s']);
        $this->assertGreaterThanOrEqual(0, $distanceCell['s']);
        $this->assertIsInt($distanceCell['g']);
        $this->assertGreaterThanOrEqual(0, $distanceCell['g']);
        $this->assertIsInt($distanceCell['n']);
        $this->assertGreaterThanOrEqual(0, $distanceCell['n']);
        $this->assertIsInt($distanceCell['b']);
        $this->assertGreaterThanOrEqual(0, $distanceCell['b']);

        $this->assertIsInt($distance);
        $this->assertGreaterThanOrEqual(0, $distance);
    }

    public static function provideAllPairs(): Generator
    {
        $points = self::getDestinationsEnum();

        foreach ($points as $from) {
            foreach ($points as $to) {
                if ($from !== $to) {
                    yield [$from, $to];
                }
            }
        }
    }

    /**
     * @dataProvider provideAllTriples
     */
    public function testDistanceForTriples(DestinationsEnum $first, DestinationsEnum $second, DestinationsEnum $third): void
    {
        $distance = $this->calculator->getDistanceBetweenDestinations($first, $second, $third);

        $this->assertIsInt($distance);
        $this->assertGreaterThanOrEqual(0, $distance);
    }

    public static function provideAllTriples(): Generator
    {
        $points = self::getDestinationsEnum();

        foreach ($points as $first) {
            foreach ($points as $second) {
                foreach ($points as $third) {
                    if ($first !== $second && $second !== $third && $first !== $third) {
                        yield [$first, $second, $third];
                    }
                }
            }
        }
    }
}