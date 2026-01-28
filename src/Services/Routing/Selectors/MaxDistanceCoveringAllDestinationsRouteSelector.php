<?php

namespace Src\Services\Routing\Selectors;

use Exception;
use Src\Services\Routing\Collections\RouteCollection;
use Src\Services\Routing\Collections\RouteDestinationCollection;

class MaxDistanceCoveringAllDestinationsRouteSelector extends DistanceBasedRouteSelector implements RouteSelector
{
    /**
     * @throws Exception
     */
    protected function applySelection(RouteCollection $routes): RouteCollection
    {
        if (! $routes->isTyped()) {
            throw new Exception('Route selection requires a typed collection.');
        }

        arsort($this->distances);
        $routeCollection = new RouteCollection();
        $visitedDestinations = [];

        /** @var RouteDestinationCollection $route */
        foreach ($this->distances as $routeId => $distance) {
            foreach ($routes->getRouteById($routeId)->getIterator() as $destination) {
                $visitedDestinations[$destination->value] = 1;
            }

            $routeCollection->add($routes->getRouteById($routeId));

            if (count($visitedDestinations) === $routes->getRouteType()->getDestinationsCount()) {
                break;
            }
        }

        return $routeCollection;
    }
}