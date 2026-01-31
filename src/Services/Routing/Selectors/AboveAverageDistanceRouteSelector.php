<?php

namespace Src\Services\Routing\Selectors;

use Exception;
use Src\Services\Routing\Collections\RouteCollection;

class AboveAverageDistanceRouteSelector extends DistanceBasedRouteSelector implements RouteSelector
{
    /**
     * @throws Exception
     */
    protected function applySelection(RouteCollection $routes): RouteCollection
    {
        arsort($this->distances);

        $avgRoutesDistance = (int)round(
            array_sum($this->distances)/count($this->distances), 0
        );

        $routeCollection = new RouteCollection();

        foreach ($this->distances as $routeId => $distance) {
            if ($distance >= $avgRoutesDistance) {
                $routeCollection->add($routes->getRouteById($routeId));
            }
        }

        return $routeCollection;
    }
}
