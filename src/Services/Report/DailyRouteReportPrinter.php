<?php

namespace Src\Services\Report;

use Src\Services\Report\Dto\RouteLegDto;

class DailyRouteReportPrinter
{
    public static function print(int $dayNumber, RouteLegDto ... $routeLegs): void
    {
        echo 'DAY ' . $dayNumber . (isset($routeLegs[0]) ? ": {$routeLegs[0]->routeType->title()}\r\n" : '');

        $dayDistance = 0;
        $dayFuel = 0;

        foreach ($routeLegs as $routeLeg) {
            $strName = mb_str_pad_right($routeLeg->name, 33);
            $strDepartureAt = mb_str_pad_right(
                "Departure at: {$routeLeg->departureAt->format('H:i')}",
                21
            );
            $strArrivalAt = mb_str_pad_right("Arrival at: {$routeLeg->arrivalAt->format('H:i')}", 19);
            $strDistance = mb_str_pad_right("Distance (km): {$routeLeg->distanceKm}", 18);
            $strFuel = mb_str_pad_right("Fuel (L): " .
                number_format($routeLeg->fuelLiters, 1, '.', ''), 20);

            echo "$strName $strDepartureAt $strArrivalAt $strDistance $strFuel" . "\r\n";
            $dayDistance += $routeLeg->distanceKm;
            $dayFuel += $routeLeg->fuelLiters;
        }

        $dayFuel = (int)round($dayFuel);

        echo "\r\n";

        echo "-------------------------   Totals: " .
            mb_str_pad_right("Distance " . $dayDistance . " (km), ", 19) .
            mb_str_pad_right("Fuel " . $dayFuel . " (L) ", 13) .
            "----------------------------------------" .
            "\r\n";

        echo "\r\n";
    }
}

