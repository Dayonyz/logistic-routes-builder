<?php

namespace Src\Services\Routing\Selectors;

use Src\Services\Distance\DistanceCalculator;
use Src\Services\Routing\Collections\RouteCollection;
use Src\Services\Routing\Collections\RouteDestinationCollection;

abstract class DistanceBasedRouteSelector
{
    protected array $distances = [];

    final public function select(RouteCollection $routes): RouteCollection
    {
        $this->calculateDistances($routes);

        return $this->applySelection($routes);
    }

    abstract protected function applySelection(RouteCollection $routes): RouteCollection;

    protected function calculateDistances(RouteCollection $routes): void
    {
        $distanceCalculator = new DistanceCalculator();

        /** @var RouteDestinationCollection $route */
        foreach ($routes->getIterator() as $route) {
            $this->distances[$route->getRouteId()] = $distanceCalculator->getDistanceBetweenDestinations(...$route->toArray());
        }
    }
}