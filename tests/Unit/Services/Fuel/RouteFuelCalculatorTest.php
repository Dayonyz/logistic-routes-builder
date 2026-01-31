<?php

namespace Tests\Unit\Services\Fuel;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Src\Enums\DestinationsEnum;
use Src\Services\Distance\DistanceCalculator;
use Src\Services\Fuel\RouteFuelCalculator;
use Src\Services\Routing\Collections\RouteDestinationCollection;

class RouteFuelCalculatorTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGetFuelByRouteCalculatesSum(): void
    {
        $route = $this->createMock(RouteDestinationCollection::class);
        $route->method('toArray')->willReturn([
            DestinationsEnum::NBR,
            DestinationsEnum::SHU,
            DestinationsEnum::LOZ
        ]);

        $mockCalculator = $this->createMock(DistanceCalculator::class);
        $mockCalculator->method('getDistanceMatrixCellBetweenDestinations')
            ->willReturn([
                'g' => 10,
                'n' => 5,
                'b' => 2,
                's' => 1
            ]);

        $calculator = new RouteFuelCalculator($mockCalculator);
        $fuel = $calculator->getFuelByRoute($route);

        $expected = round(10*0.089 + 5*0.089 + 2*0.089*1.15, 1) * 2;
        $expected = (int) round($expected);

        $this->assertIsInt($fuel);
        $this->assertEquals($expected, $fuel);
    }

    public function testGetFuelBetweenDestinationsInsideCity(): void
    {
        $mockCalculator = $this->createMock(DistanceCalculator::class);
        $mockCalculator->method('getDistanceMatrixCellBetweenDestinations')
            ->willReturn(['s' => 1]); // движение внутри города

        $calculator = new RouteFuelCalculator($mockCalculator);

        $fuel = $calculator->getFuelBetweenDestinations(
            DestinationsEnum::LOZ,
            DestinationsEnum::LOZ
        );

        $this->assertEquals(round(0.089 * 1, 1), $fuel);
    }

    public function testGetFuelBetweenDestinationsBetweenCities(): void
    {
        $mockCalculator = $this->createMock(DistanceCalculator::class);
        $mockCalculator->method('getDistanceMatrixCellBetweenDestinations')
            ->willReturn([
                'g' => 10,
                'n' => 5,
                'b' => 2
            ]);

        $calculator = new RouteFuelCalculator($mockCalculator);

        $fuel = $calculator->getFuelBetweenDestinations(
            DestinationsEnum::NBR,
            DestinationsEnum::SHU
        );

        $expected = round(10*0.089*0.85 + 5*0.089 + 2*0.089*1.15, 1);
        $this->assertEquals($expected, $fuel);
    }
}
