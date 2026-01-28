<?php

namespace Src\Commands;

use Exception;
use Src\Services\Distance\DistanceCalculator;
use Src\Services\Fuel\FuelLimitsCalculator;
use Src\Services\Fuel\RouteFuelCalculator;
use Src\Services\Report\DailyRouteReportBuilder;
use Src\Services\Report\DailyRouteReportPrinter;
use Src\Services\Routing\Collections\RouteDestinationCollection;
use Src\Services\Schedule\DailyRouteGenerator;
use Src\Services\Schedule\RouteTimeTracker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class RouteGeneratorCommand extends Command
{
    protected static $defaultName = 'app:generate-routes';
    private const PRECISION_DIVISOR = 3;

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $daysCount = $helper->ask(
            $input,
            $output,
            new Question('Enter the number of working days in a month: ')
        );

        $output->writeln("Days count: $daysCount");

        $output->writeln("Wait a little, detecting fuel limits...");


        $fuelLimits = (new FuelLimitsCalculator)->forDays($daysCount);

        $monthlyFuelConsumption = 0;

        while (
            $monthlyFuelConsumption < $fuelLimits->minFuelLiters ||
            $monthlyFuelConsumption > $fuelLimits->maxFuelLiters
        ) {
            $monthlyFuelConsumption = $helper->ask(
                $input,
                $output,
                new Question("Enter the monthly fuel consumption " .
                    "between {$fuelLimits->minFuelLiters} and {$fuelLimits->maxFuelLiters} (L): "
                )
            );
        }

        $goodWeatherPercent = 0;

        while (
            $goodWeatherPercent < 1 ||
            $goodWeatherPercent > 100
        ) {
            $goodWeatherPercent = $helper->ask(
                $input,
                $output,
                new Question("Enter the probability of good weather for the past month (between 1-100): ")
            );
        }

        $totalFuel = 0;
        $totalDistance = 0;
        $dailyReports = [];
        $dailyRouteGenerator = new DailyRouteGenerator();

        while (
            round($totalFuel / self::PRECISION_DIVISOR) <
            round($monthlyFuelConsumption / self::PRECISION_DIVISOR) ||
            round($totalFuel / self::PRECISION_DIVISOR) >
            round($monthlyFuelConsumption / self::PRECISION_DIVISOR)
        ) {
            try {
                $totalFuel = 0;
                $totalDistance = 0;
                $dailyReports = [];
                $routes = $dailyRouteGenerator->generateRoutes($daysCount);
                $distanceCalculator = new DistanceCalculator();
                $routeFuelCalculator = new RouteFuelCalculator();
                $dailyRouteReportBuilder = new DailyRouteReportBuilder();

                /**
                 * @var RouteDestinationCollection $route
                 */
                foreach ($routes->getIterator() as $route) {
                    $timeTracker = new RouteTimeTracker($goodWeatherPercent);
                    $dailyReports[] = $dailyRouteReportBuilder->build($route, $timeTracker);
                    $totalFuel += $routeFuelCalculator->getFuelByRoute($route);
                    $totalDistance += $distanceCalculator->getDistanceBetweenDestinations(...$route->toArray());
                }

            } catch (Exception $e) {
                echo $e->getMessage() . "\r\n";
            }
        }

        foreach ($dailyReports as $index => $routeLegs) {
            DailyRouteReportPrinter::print($index + 1, ...$routeLegs);
        }

        echo '______________________________________________________________________________________________________' .
            '______' .
            "\r\n";

        echo 'Total monthly distance: ' . $totalDistance . ' (km)' . "\r\n";
        echo 'Total monthly fuel consumption: ' . $totalFuel . ' (L)' . "\r\n";

        return Command::SUCCESS;
    }
}
