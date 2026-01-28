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

    public function __construct(
        ?DistanceCalculator $distanceCalculator = null,
        ?RouteFuelCalculator $routeFuelCalculator = null
    ) {
        $this->distanceCalculator = $distanceCalculator ?? new DistanceCalculator();
        $this->routeFuelCalculator = $routeFuelCalculator ?? new RouteFuelCalculator();
    }

    /**
     * @return RouteLegDto[]
     * @throws Exception
     */
    public function build(RouteDestinationCollection $destinations, RouteTimeTracker $timeTracker): array
    {
        $routeLegs = [];

        $routeType = $destinations->getRouteType();
        $destinations = $destinations->toArray();

        for ($i = 0; $i < count($destinations) - 1; $i++) {
            $distanceMatrixCell = $this->distanceCalculator->getDistanceMatrixCellBetweenDestinations(
                $destinations[$i],
                $destinations[$i+1]
            );

            $name = $destinations[$i]->title() . ' - ' . $destinations[$i+1]->title();
            $distance = $distanceMatrixCell['s'];

            $from = $destinations[$i];
            $to = $destinations[$i+1];
            $timeTracker->setDestinations($from, $to);
            $fuel = $this->routeFuelCalculator->getFuelBetweenDestinations($from, $to);
            $departureAt = $timeTracker->getStartMovingTime();
            $timeTracker->calculateMovingTime($distanceMatrixCell);
            $arrivalAt = $timeTracker->getEndMovingTime();

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

            $timeTracker->generateNextStartMovingTime();
        }

        return $routeLegs;
    }
}