<?php

namespace Tests\Unit\Services\Routing\Selectors;

use Exception;
use PHPUnit\Framework\TestCase;
use Src\Enums\RouteTypesEnum;
use Src\Services\Routing\Selectors\MaxDistanceCoveringAllDestinationsRouteSelector;
use Src\Services\Routing\Collections\RouteCollection;
use Src\Services\Routing\Collections\RouteDestinationCollection;

class MaxDistanceCoveringAllDestinationsRouteSelectorTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testStopsWhenAllDestinationsAreCoveredForVil(): void
    {
        $routes = new RouteCollection();

        $vilDestinations = RouteTypesEnum::VIL->getDestinations();

        $chunk1 = array_slice($vilDestinations, 0, 3);
        $chunk2 = array_slice($vilDestinations, 3);

        $r1 = new RouteDestinationCollection();
        $r1->add(...$chunk1);

        $r2 = new RouteDestinationCollection();
        $r2->add(...$chunk2);

        $r3 = new RouteDestinationCollection();
        $r3->add(...$chunk1);

        $routes->add($r1, $r2, $r3);

        $selector = new class extends MaxDistanceCoveringAllDestinationsRouteSelector {
            public function setDistances(array $distances): void { $this->distances = $distances; }
            public function select(RouteCollection $routes): RouteCollection
            {
                return $this->applySelection($routes);
            }
        };

        $selector->setDistances([
            $r1->getRouteId() => 100,
            $r2->getRouteId() => 90,
            $r3->getRouteId() => 10,
        ]);

        $result = $selector->select($routes);

        $this->assertSame(2, $result->getRoutesCount());
    }
}
