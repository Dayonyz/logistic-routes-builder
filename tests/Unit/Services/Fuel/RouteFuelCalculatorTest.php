<?php

namespace Tests\Unit\Services\Fuel;

use PHPUnit\Framework\TestCase;
use Src\Enums\DestinationsEnum;
use Src\Services\Distance\DistanceCalculator;
use Src\Services\Fuel\RouteFuelCalculator;
use Src\Services\Routing\Collections\RouteDestinationCollection;
use Mockery;

class RouteFuelCalculatorTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testGetFuelByRouteCalculatesSum(): void
    {
        $route = Mockery::mock(RouteDestinationCollection::class);
        $route->shouldReceive('toArray')->andReturn([
            DestinationsEnum::NBR,
            DestinationsEnum::SHU,
            DestinationsEnum::LOZ
        ]);

        $mockCalculator = Mockery::mock(DistanceCalculator::class);
        $mockCalculator->shouldReceive('getDistanceMatrixCellBetweenDestinations')
            ->andReturn([
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
        $mockCalculator = Mockery::mock(DistanceCalculator::class);
        $mockCalculator->shouldReceive('getDistanceMatrixCellBetweenDestinations')
            ->andReturn(['s' => 1]); // движение внутри города

        $calculator = new RouteFuelCalculator($mockCalculator);

        $fuel = $calculator->getFuelBetweenDestinations(
            DestinationsEnum::LOZ,
            DestinationsEnum::LOZ
        );

        $this->assertEquals(round(0.089 * 1, 1), $fuel);
    }

    public function testGetFuelBetweenDestinationsBetweenCities(): void
    {
        $mockCalculator = Mockery::mock(DistanceCalculator::class);
        $mockCalculator->shouldReceive('getDistanceMatrixCellBetweenDestinations')
            ->andReturn([
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