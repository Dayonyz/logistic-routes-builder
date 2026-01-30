<?php

namespace Src\Services\Schedule;

use Exception;
use Src\Services\Routing\Collections\RouteCollection;

class DailyRouteGenerator
{
    protected DailyRouteTypesGenerator $routeTypesGenerator;
    protected RandomRoutePicker $randomRoutePicker;

    public function __construct(?DailyRouteTypesGenerator $routeTypesGenerator = null) {
        $this->routeTypesGenerator = $routeTypesGenerator ?? new DailyRouteTypesGenerator();
    }

    /**
     * @throws Exception
     */
    public function generateRoutes(int $daysCount, $scipRestrictions = false): RouteCollection
    {
        $routeTypes = $this->routeTypesGenerator->generateSchedule($daysCount, $scipRestrictions);

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