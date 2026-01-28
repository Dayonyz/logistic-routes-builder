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

    public static function getFuelByRoute(RouteDestinationCollection $route): int | float
    {
        $destinations = $route->toArray();
        $totalFuel = 0.0;
        $count = count($destinations);

        for ($i = 0; $i < $count - 1; $i++) {
            $totalFuel += static::getFuelBetweenDestinations(
                $destinations[$i],
                $destinations[$i + 1]
            );
        }

        return (int) round($totalFuel);
    }

    public static function getFuelBetweenDestinations(DestinationsEnum $from, DestinationsEnum $to): float
    {
        $distanceCell = (new DistanceCalculator())->getDistanceMatrixCellBetweenDestinations($from, $to);

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