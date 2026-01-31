<?php

namespace Tests\Unit\Services\Routing\Selectors;

use Exception;
use PHPUnit\Framework\TestCase;
use Src\Services\Routing\Selectors\DistanceBasedRouteSelector;
use Src\Services\Routing\Collections\RouteCollection;
use Src\Services\Routing\Collections\RouteDestinationCollection;
use Src\Enums\DestinationsEnum;

class DistanceBasedRouteSelectorTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCalculateDistancesPopulatesDistancesArray(): void
    {
        $route1 = new RouteDestinationCollection();
        $route1->add(DestinationsEnum::LOZ);

        $route2 = new RouteDestinationCollection();
        $route2->add(DestinationsEnum::BLY);

        $routes = new RouteCollection();
        $routes->add($route1, $route2);

        $selector = new class extends DistanceBasedRouteSelector {
            public function calculateDistances(RouteCollection $routes): void
            {
                foreach ($routes->getIterator() as $route) {
                    $this->distances[$route->getRouteId()] = match ($route->toArray()[0]) {
                        DestinationsEnum::LOZ => 10,
                        DestinationsEnum::BLY => 20,
                    };
                }
            }

            protected function applySelection(RouteCollection $routes): RouteCollection
            {
                return $routes;
            }
        };

        $selector->select($routes);

        $distances = $selector->getDistances();

        $this->assertCount(2, $distances);
        $this->assertContains(10, $distances);
        $this->assertContains(20, $distances);
    }
}
