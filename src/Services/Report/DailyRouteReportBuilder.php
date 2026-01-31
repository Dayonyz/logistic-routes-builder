<?php

namespace Src\Services\Report;

use Exception;
use Src\Services\Distance\DistanceCalculator;
use Src\Services\Fuel\RouteFuelCalculator;
use Src\Services\Report\Dto\RouteLegDto;
use Src\Services\Routing\Collections\RouteDestinationCollection;
use Src\Services\Schedule\RouteTimeTracker;

class DailyRouteReportBuilder
{
    private DistanceCalculator $distanceCalculator;
    private RouteFuelCalculator $routeFuelCalculator;
    private RouteTimeTracker $routeTimeTracker;

    public function __construct(
        RouteTimeTracker $routeTimeTracker,
        ?DistanceCalculator $distanceCalculator = null,
        ?RouteFuelCalculator $routeFuelCalculator = null
    ) {
        $this->routeTimeTracker = $routeTimeTracker;
        $this->distanceCalculator = $distanceCalculator ?? new DistanceCalculator();
        $this->routeFuelCalculator = $routeFuelCalculator ?? new RouteFuelCalculator();
    }

    /**
     * @return RouteLegDto[]
     * @throws Exception
     */
    public function build(RouteDestinationCollection $destinations): array
    {
        $routeLegs = [];

        $routeType = $destinations->getType();
        $destinations = $destinations->toArray();

        for ($i = 0; $i < count($destinations) - 1; $i++) {
            $from = $destinations[$i];
            $to = $destinations[$i+1];

            $distanceMatrixCell = $this->distanceCalculator->getDistanceMatrixCellBetweenDestinations(
                $from,
                $to
            );

            $name = $destinations[$i]->title() . ' - ' . $destinations[$i+1]->title();
            $distance = $distanceMatrixCell['s'];

            $this->routeTimeTracker->setDestinations($from, $to);
            $fuel = $this->routeFuelCalculator->getFuelBetweenDestinations($from, $to);
            $departureAt = $this->routeTimeTracker->getStartMovingTime();
            $this->routeTimeTracker->calculateMovingTime($distanceMatrixCell);
            $arrivalAt = $this->routeTimeTracker->getEndMovingTime();

            $routeLegs[] = new RouteLegDto(
                $routeType,
                $name,
                $from,
                $to,
                $departureAt,
                $arrivalAt,
                $distance,
                $fuel
            );
        }

        return $routeLegs;
    }
}