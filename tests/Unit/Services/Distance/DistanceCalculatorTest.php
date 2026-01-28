<?php

namespace Tests\Unit\Services\Distance;

use JsonException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Src\Enums\DestinationsEnum;
use Src\Services\Distance\DistanceCalculator;

class DistanceCalculatorTest extends TestCase
{
    private DistanceCalculator $calculator;

    /**
     * @throws JsonException
     */
    protected function setUp(): void
    {
        $this->calculator = new DistanceCalculator(__DIR__ . '/matrix.json');
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
        $this->assertEquals(4, $cell['s']); // проверка конкретного значения
    }

    public function testGetDistanceBetweenDestinationsSum(): void
    {
        $distance = $this->calculator->getDistanceBetweenDestinations(
            DestinationsEnum::ZLP,
            DestinationsEnum::OLE,
            DestinationsEnum::BLV
        );

        // zlp->ole = 4, ole->blv = 22, сумма = 26
        $this->assertEquals(26, $distance);
    }

    public function testGetDistanceThrowsExceptionForInvalidRoute(): void
    {
        $this->expectException(RuntimeException::class);

        $this->calculator->getDistanceBetweenDestinations(
            DestinationsEnum::ZLP,
            DestinationsEnum::XXX
        );
    }

    public function testEmptyRouteReturnsZeroOrThrows(): void
    {
        $this->expectException(RuntimeException::class);

        $this->calculator->getDistanceBetweenDestinations();
    }
}