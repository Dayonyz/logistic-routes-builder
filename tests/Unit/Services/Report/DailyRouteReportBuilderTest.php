<?php

namespace Tests\Unit\Services\Report;

use DateTimeImmutable;
use Exception;
use PHPUnit\Framework\TestCase;
use Src\Enums\DestinationsEnum;
use Src\Services\Report\DailyRouteReportBuilder;
use Src\Services\Routing\Collections\RouteDestinationCollection;
use Src\Services\Schedule\DailyRouteGenerator;
use Tests\Unit\Services\Schedule\DeterministicRouteTimeTracker;

final class DailyRouteReportBuilderTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testBuildCreatesCorrectRouteLegs(): void
    {
        $dailyRouteGenerator = new DailyRouteGenerator();

        $routes = $dailyRouteGenerator->generateRoutes(2);

        $routeTimeTracker = new DeterministicRouteTimeTracker(
            goodWeatherPercent: 100,
            fluctuation: 1.0
        );

        $builder = new DailyRouteReportBuilder($routeTimeTracker);

        /**
         * @var RouteDestinationCollection $destinations
         */
        foreach ($routes->getIterator() as $destinations) {
            $routeLegs = $builder->build($destinations);
            $destinationsArr = $destinations->toArray();

            $this->assertCount(count($destinationsArr) - 1, $routeLegs);

            foreach ($routeLegs as $index => $leg) {
                $this->assertSame(
                    $destinations->getType(),
                    $leg->routeType
                );

                $this->assertNotEmpty($leg->name);

                $this->assertInstanceOf(DateTimeImmutable::class, $leg->departureAt);
                $this->assertInstanceOf(DateTimeImmutable::class, $leg->arrivalAt);

                $this->assertInstanceOf(DestinationsEnum::class, $leg->from);
                $this->assertInstanceOf(DestinationsEnum::class, $leg->to);

                $this->assertEquals($leg->from, $destinationsArr[$index]);
                $this->assertEquals($leg->to, $destinationsArr[$index + 1]);

                $this->assertTrue(
                    $leg->arrivalAt > $leg->departureAt,
                    'Arrival time must be after departure time'
                );

                $this->assertGreaterThan(0, $leg->distanceKm);
                $this->assertGreaterThan(0, $leg->fuelLiters);

                $this->assertTrue(
                    $routeLegs[1]->departureAt >= $routeLegs[0]->arrivalAt,
                    'Second leg must start after or at end of first leg'
                );
            }
        }
    }
}

