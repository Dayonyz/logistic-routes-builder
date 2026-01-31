<?php

namespace Tests\Unit\Services\Routing\Selectors;

use Exception;
use PHPUnit\Framework\TestCase;
use Src\Services\Routing\Selectors\AboveAverageDistanceRouteSelector;
use Src\Services\Routing\Collections\RouteCollection;
use Src\Services\Routing\Collections\RouteDestinationCollection;
use Src\Enums\DestinationsEnum;
use Src\Services\Distance\DistanceCalculator;

class AboveAverageDistanceRouteSelectorTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testSelectReturnsRoutesAboveAverageDistance(): void
    {
        $route1 = new RouteDestinationCollection();
        $route1->add(DestinationsEnum::LOZ);

        $route2 = new RouteDestinationCollection();
        $route2->add(DestinationsEnum::BLY);

        $route3 = new RouteDestinationCollection();
        $route3->add(DestinationsEnum::MYR);

        $routes = new RouteCollection();
        $routes->add($route1, $route2, $route3);

        $calculatorMock = $this->getMockBuilder(DistanceCalculator::class)
            ->onlyMethods(['getDistanceBetweenDestinations'])
            ->getMock();

        $calculatorMock->method('getDistanceBetweenDestinations')->willReturnCallback(
            fn(...$args) => match ($args) {
                [DestinationsEnum::LOZ] => 10,
                [DestinationsEnum::BLY] => 20,
                [DestinationsEnum::MYR] => 30,
                default => 0
            }
        );

        $selector = new class($calculatorMock) extends AboveAverageDistanceRouteSelector {
            public function __construct(private readonly DistanceCalculator $calculator) {}
            protected function calculateDistances(RouteCollection $routes): void
            {
                foreach ($routes->getIterator() as $route) {
                    $this->distances[$route->getRouteId()] =
                        $this->calculator->getDistanceBetweenDestinations(...$route->toArray());
                }
            }
        };

        $selectedRoutes = $selector->select($routes);

        $this->assertInstanceOf(RouteCollection::class, $selectedRoutes);

        $average = array_sum($selector->getDistances()) / count($selector->getDistances());

        foreach ($selectedRoutes->getIterator() as $route) {
            $this->assertGreaterThanOrEqual(
                $average,
                $selector->getDistances()[$route->getRouteId()]
            );
        }
    }
}

