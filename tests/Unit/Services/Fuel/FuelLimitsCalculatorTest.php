<?php

namespace Tests\Unit\Services\Fuel;

use ArrayIterator;
use Exception;
use PHPUnit\Framework\TestCase;
use Src\Services\Fuel\FuelLimitsCalculator;
use Src\Services\Fuel\RouteFuelCalculator;
use Src\Services\Routing\Collections\RouteCollection;
use Src\Services\Routing\Collections\RouteDestinationCollection;
use Src\Services\Schedule\DailyRouteGenerator;

class FuelLimitsCalculatorTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testForDaysReturnsFuelLimitsDto(): void
    {
        $mockRoute = $this->createMock(RouteDestinationCollection::class);

        $mockRouteCollection = $this->createMock(RouteCollection::class);
        $mockRouteCollection->method('getIterator')
            ->willReturn(new ArrayIterator([$mockRoute]));

        $mockDailyGenerator = $this->createMock(DailyRouteGenerator::class);
        $mockDailyGenerator->method('generateRoutes')
            ->with(5, true)
            ->willReturn($mockRouteCollection);

        $mockCalculator = $this->createMock(RouteFuelCalculator::class);
        $mockCalculator->method('getFuelByRoute')
            ->with($mockRoute)
            ->willReturn(10);

        $calculator = new FuelLimitsCalculator($mockCalculator, $mockDailyGenerator);

        $result = $calculator->forDays(5, 1);

        $this->assertEquals(10, $result->minFuelLiters);
        $this->assertEquals(10, $result->maxFuelLiters);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testForDaysCalculatesMinAndMaxFuel(): void
    {
        $mockRoute = $this->createMock(RouteDestinationCollection::class);

        $mockRouteCollection = $this->createMock(RouteCollection::class);
        $mockRouteCollection->method('getIterator')
            ->willReturn(new ArrayIterator([$mockRoute]));

        $mockDailyGenerator = $this->createMock(DailyRouteGenerator::class);
        $mockDailyGenerator->method('generateRoutes')
            ->willReturn($mockRouteCollection);

        $fuelSequence = [5, 10, 7];
        $iteration = 0;

        $mockCalculator = $this->createMock(RouteFuelCalculator::class);
        $mockCalculator->method('getFuelByRoute')
            ->willReturnCallback(function () use (&$iteration, $fuelSequence) {
                return $fuelSequence[$iteration++];
            });

        $calculator = new FuelLimitsCalculator($mockCalculator, $mockDailyGenerator);

        $result = $calculator->forDays(1, 3);

        $this->assertEquals(5, $result->minFuelLiters);
        $this->assertEquals(10, $result->maxFuelLiters);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testForDaysCatchesExceptionsAndContinues(): void
    {
        $mockRoute = $this->createMock(RouteDestinationCollection::class);

        $mockRouteCollection = $this->createMock(RouteCollection::class);
        $mockRouteCollection->method('getIterator')
            ->willReturn(new ArrayIterator([$mockRoute]));

        $mockDailyGenerator = $this->createMock(DailyRouteGenerator::class);
        $mockDailyGenerator->method('generateRoutes')
            ->willReturn($mockRouteCollection);

        $fuelSequence = [
            10,
            function() { throw new Exception('Test error'); },
            20
        ];
        $iteration = 0;

        $mockCalculator = $this->createMock(RouteFuelCalculator::class);
        $mockCalculator->method('getFuelByRoute')
            ->willReturnCallback(function () use (&$iteration, $fuelSequence) {
                $val = $fuelSequence[$iteration++];
                if (is_callable($val)) {
                    return $val();
                }
                return $val;
            });

        $calculator = new FuelLimitsCalculator($mockCalculator, $mockDailyGenerator);

        $this->expectOutputString("Test error\r\n");

        $result = $calculator->forDays(1, 3);

        $this->assertEquals(10, $result->minFuelLiters);
        $this->assertEquals(20, $result->maxFuelLiters);
    }
}
