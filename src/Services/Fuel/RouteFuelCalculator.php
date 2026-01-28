<?php

namespace Src\Services\Fuel;

use Src\Enums\DestinationsEnum;
use Src\Services\Distance\DistanceCalculator;
use Src\Services\Routing\Collections\RouteDestinationCollection;

class RouteFuelCalculator
{
    private const INSIDE_CITY = 0.089;
    private const BETWEEN_CITY = [
        'g' => 0.089*0.85,
        'n' => 0.089,
        'b' => 0.089*1.15,
    ];

    private DistanceCalculator $distanceCalculator;

    public function __construct(?DistanceCalculator $distanceCalculator = null)
    {
        $this->distanceCalculator = $distanceCalculator ?? new DistanceCalculator();
    }

    public function getFuelByRoute(RouteDestinationCollection $route): int | float
    {
        $destinations = $route->toArray();
        $totalFuel = 0.0;
        $count = count($destinations);

        for ($i = 0; $i < $count - 1; $i++) {
            $totalFuel += $this->getFuelBetweenDestinations(
                $destinations[$i],
                $destinations[$i + 1]
            );
        }

        return (int) round($totalFuel);
    }

    public function getFuelBetweenDestinations(DestinationsEnum $from, DestinationsEnum $to): float
    {
        $distanceCell = $this->distanceCalculator->getDistanceMatrixCellBetweenDestinations($from, $to);

        // Inside city movement
        if ($from === $to && in_array($from, [DestinationsEnum::ZLP, DestinationsEnum::LOZ], true)) {
            return round($distanceCell['s'] * self::INSIDE_CITY, 1);
        }

        // Between cities
        $fuel = 0.0;

        foreach (self::BETWEEN_CITY as $roadQuality => $coefficient) {
            if (! isset($distanceCell[$roadQuality])) {
                continue;
            }

            $fuel += $distanceCell[$roadQuality] * $coefficient;
        }

        return round($fuel, 1);
    }
}