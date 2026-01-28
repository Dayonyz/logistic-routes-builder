<?php

namespace Src\Services\Fuel;

use Exception;
use Src\Services\Fuel\Dto\FuelLimitsDto;
use Src\Services\Routing\Collections\RouteDestinationCollection;
use Src\Services\Schedule\DailyRouteGenerator;

class FuelLimitsCalculator
{
    private RouteFuelCalculator $routeFuelCalculator;
    private DailyRouteGenerator $dailyRouteGenerator;

    public function __construct(
        ?RouteFuelCalculator $routeFuelCalculator = null,
        ?DailyRouteGenerator $dailyRouteGenerator = null
    ) {
        $this->routeFuelCalculator = $routeFuelCalculator ?? new RouteFuelCalculator();
        $this->dailyRouteGenerator = $dailyRouteGenerator ?? new DailyRouteGenerator();
    }

    public function forDays(int $daysCount, int $iterationCount = 150): FuelLimitsDto
    {
        $fuelLimits = new FuelLimitsDto(0, 0);

        for ($i = 0; $i < $iterationCount; $i++) {
            try {
                $totalFuel = 0;

                $routes = $this->dailyRouteGenerator->generateRoutes($daysCount, true);

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

