<?php

namespace Tests\Unit\Services\Fuel;

use ArrayIterator;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;
use Src\Services\Fuel\FuelLimitsCalculator;
use Src\Services\Fuel\RouteFuelCalculator;
use Src\Services\Routing\Collections\RouteCollection;
use Src\Services\Routing\Collections\RouteDestinationCollection;
use Src\Services\Schedule\DailyRouteGenerator;

class FuelLimitsCalculatorTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testForDaysReturnsFuelLimitsDto(): void
    {
        $mockDestinationCollection = Mockery::mock(RouteDestinationCollection::class);

        $mockRouteCollection = Mockery::mock(RouteCollection::class);
        $mockRouteCollection->shouldReceive('getIterator')
            ->andReturn(new ArrayIterator([$mockDestinationCollection]));

        Mockery::mock('alias:' . DailyRouteGenerator::class)
            ->shouldReceive('generateRoutes')
            ->with(5, true)
            ->andReturn($mockRouteCollection);

        $mockCalculator = Mockery::mock(RouteFuelCalculator::class);
        $mockCalculator->shouldReceive('getFuelByRoute')
            ->with($mockDestinationCollection)
            ->andReturn(10);

        $calculator = new FuelLimitsCalculator($mockCalculator);

        $result = $calculator->forDays(5, 1);

        $this->assertEquals(10, $result->minFuelLiters);
        $this->assertEquals(10, $result->maxFuelLiters);
    }

    public function testForDaysCalculatesMinAndMaxFuel(): void
    {
        $mockDestinationCollection = Mockery::mock(RouteDestinationCollection::class);
        $mockRouteCollection = Mockery::mock(RouteCollection::class);
        $mockRouteCollection->shouldReceive('getIterator')
            ->andReturn(new ArrayIterator([$mockDestinationCollection]));

        Mockery::mock('alias:' . DailyRouteGenerator::class)
            ->shouldReceive('generateRoutes')
            ->andReturn($mockRouteCollection);

        $fuelSequence = [5, 10, 7];
        $iteration = 0;

        $mockCalculator = Mockery::mock(RouteFuelCalculator::class);
        $mockCalculator->shouldReceive('getFuelByRoute')
            ->andReturnUsing(function () use (&$iteration, $fuelSequence, $mockDestinationCollection) {
                return $fuelSequence[$iteration++];
            });

        $calculator = new FuelLimitsCalculator($mockCalculator);

        $result = $calculator->forDays(1, 3);

        $this->assertEquals(5, $result->minFuelLiters);
        $this->assertEquals(10, $result->maxFuelLiters);
    }

    public function testForDaysCatchesExceptionsAndContinues(): void
    {
        $mockDestinationCollection = Mockery::mock(RouteDestinationCollection::class);
        $mockRouteCollection = Mockery::mock(RouteCollection::class);

        $mockRouteCollection->shouldReceive('getIterator')
            ->andReturn(new ArrayIterator([$mockDestinationCollection]));

        Mockery::mock('alias:' . DailyRouteGenerator::class)
            ->shouldReceive('generateRoutes')
            ->andReturn($mockRouteCollection);

        $fuelSequence = [10, function() { throw new Exception('Test error'); }, 20];
        $iteration = 0;

        $mockCalculator = Mockery::mock(RouteFuelCalculator::class);
        $mockCalculator->shouldReceive('getFuelByRoute')
            ->andReturnUsing(function () use (&$iteration, $fuelSequence, $mockDestinationCollection) {
                $val = $fuelSequence[$iteration++];
                if (is_callable($val)) {
                    return $val();
                }
                return $val;
            });

        $calculator = new FuelLimitsCalculator($mockCalculator);

        $this->expectOutputString("Test error\r\n");
        $result = $calculator->forDays(1, 3);

        $this->assertEquals(10, $result->minFuelLiters);
        $this->assertEquals(20, $result->maxFuelLiters);
    }
}