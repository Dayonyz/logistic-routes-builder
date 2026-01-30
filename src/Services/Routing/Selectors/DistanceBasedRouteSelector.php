<?php

namespace Src\Services\Routing\Selectors;

use Src\Services\Distance\DistanceCalculator;
use Src\Services\Routing\Collections\RouteCollection;
use Src\Services\Routing\Collections\RouteDestinationCollection;

abstract class DistanceBasedRouteSelector implements RouteSelector
{
    protected DistanceCalculator $distanceCalculator;
    protected array $distances = [];

    public function __construct(? DistanceCalculator $distanceCalculator = null)
    {
        $this->distanceCalculator = $distanceCalculator ?? new DistanceCalculator();
    }

    public function select(RouteCollection $routes): RouteCollection
    {
        $this->calculateDistances($routes);

        return $this->applySelection($routes);
    }

    public function getDistances(): array
    {
        return $this->distances;
    }

    abstract protected function applySelection(RouteCollection $routes): RouteCollection;

    protected function calculateDistances(RouteCollection $routes): void
    {
        /** @var RouteDestinationCollection $route */
        foreach ($routes->getIterator() as $route) {
            $this->distances[$route->getRouteId()] = $this->distanceCalculator
                ->getDistanceBetweenDestinations(
                    ...$route->toArray()
                );
        }
    }
}