<?php

namespace Src\Services\Routing\Selectors;

use Exception;
use Src\Enums\RouteTypesEnum;
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
            throw new Exception('Route selection requires a typed route collection.');
        }

        arsort($this->distances);

        $routeTypeDestinations = [];

        foreach (RouteTypesEnum::cases() as $routeTypesEnum) {
            $routeTypeDestinations[$routeTypesEnum->value] = [];

            foreach ($routeTypesEnum->getDestinations() as $destination) {
                $routeTypeDestinations[$routeTypesEnum->value][$destination->value] = 1;
            }
        }

        $routeCollection = new RouteCollection();
        $visitedDestinations = [];

        /** @var RouteDestinationCollection $route */
        foreach ($this->distances as $routeId => $distance) {
            $route = $routes->getRouteById($routeId);

            foreach ($route->getIterator() as $destination) {
                $visitedDestinations[$destination->value] = 1;
            }

            $routeCollection->add($route);

            if (count($visitedDestinations) === count($routeTypeDestinations[$route->getType()->value]) &&
                array_flip($visitedDestinations) === array_flip($routeTypeDestinations[$route->getType()->value])
            ) {
                break;
            }
        }

        return $routeCollection;
    }
}
