<?php

namespace Tests\Unit\Services\Distance;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Src\Enums\DestinationsEnum;
use Src\Services\Distance\DistanceCalculator;

class DistanceCalculatorTest extends TestCase
{
    private DistanceCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new class(__DIR__ . '/matrix.json') extends DistanceCalculator {
            public function get(string $from, string $to): array
            {
                return $this->matrix[$from][$to]
                    ?? throw new RuntimeException("Distance cell not found: {$from} -> {$to}");
            }
        };
    }

    public function testConstructorThrowsExceptionForMissingFile(): void
    {
        $this->expectException(RuntimeException::class);

        new DistanceCalculator('/path/to/nonexistent.json');
    }

    public function testGetDistanceMatrixCellBetweenDestinations(): void
    {
        $cell = $this->calculator->getDistanceMatrixCellBetweenDestinations(
            DestinationsEnum::ZLP,
            DestinationsEnum::OLE
        );

        $this->assertIsArray($cell);
        $this->assertArrayHasKey('s', $cell);
        $this->assertEquals(4, $cell['s']);
    }

    public function testGetDistanceBetweenDestinationsSum(): void
    {
        $distance = $this->calculator->getDistanceBetweenDestinations(
            DestinationsEnum::ZLP,
            DestinationsEnum::OLE,
            DestinationsEnum::BLV
        );

        $this->assertEquals(26, $distance);
    }

    public function testGetDistanceThrowsExceptionForInvalidRoute(): void
    {
        $this->expectException(RuntimeException::class);

        $this->calculator->get(
            DestinationsEnum::ZLP->value,
            'xxx'
        );
    }

    public function testEmptyRouteReturnsZeroOrThrows(): void
    {
        $this->expectException(RuntimeException::class);

        $this->calculator->getDistanceBetweenDestinations();
    }
}
