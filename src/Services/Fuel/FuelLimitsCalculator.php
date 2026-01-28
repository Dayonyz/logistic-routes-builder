<?php

namespace Src\Services\Fuel;

use Exception;
use Src\Services\Distance\DistanceCalculator;
use Src\Services\Fuel\Dto\FuelLimitsDto;
use Src\Services\Routing\Collections\RouteDestinationCollection;
use Src\Services\Schedule\DailyRouteGenerator;

class FuelLimitsCalculator
{
    private RouteFuelCalculator $routeFuelCalculator;

    public function __construct(?RouteFuelCalculator $routeFuelCalculator = null)
    {
        $this->routeFuelCalculator = $routeFuelCalculator ?? new RouteFuelCalculator(new DistanceCalculator());
    }

    public function forDays(int $daysCount, int $iterationCount = 150): FuelLimitsDto
    {
        $fuelLimits = new FuelLimitsDto(0, 0);

        for ($i = 0; $i < $iterationCount; $i++) {
            try {
                $totalFuel = 0;

                $routes = DailyRouteGenerator::generateRoutes($daysCount, true);
                /**
                 * @var RouteDestinationCollection $route
                 */
                foreach ($routes->getIterator() as $route) {
                    $totalFuel += $this->routeFuelCalculator->getFuelByRoute($route);
                }

                if ($i === 0) {
                    $fuelLimits->minFuelLiters = $totalFuel;
                    $fuelLimits->maxFuelLiters = $totalFuel;
                } else {
                    if ($totalFuel > $fuelLimits->maxFuelLiters) {
                        $fuelLimits->maxFuelLiters = $totalFuel;
                    }

                    if ($totalFuel < $fuelLimits->minFuelLiters) {
                        $fuelLimits->minFuelLiters = $totalFuel;
                    }
                }
            } catch (Exception $e) {
                echo $e->getMessage() . "\r\n";
            }
        }

        return $fuelLimits;
    }
}

