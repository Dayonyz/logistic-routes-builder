<?php

namespace Src\Services\Schedule;

use Exception;
use Src\Services\Routing\Collections\RouteCollection;

class DailyRouteGenerator
{
    /**
     * @throws Exception
     */
    public static function generateRoutes(int $daysCount, $scipRestrictions = false): RouteCollection
    {
        $routeTypes = (new DailyRouteTypesGenerator())->generateSchedule($daysCount, $scipRestrictions);

        $prevRoute = null;
        $routeCollection = new RouteCollection();
        try {
            foreach ($routeTypes as $routeType) {
                $routePicker = new RandomRoutePicker($routeType);
                $route = $routePicker->getNextRoute($prevRoute, $scipRestrictions);
                $routeCollection->add($route);
                $prevRoute = $route;
            }
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }

        return $routeCollection;
    }
}