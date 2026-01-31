<?php

namespace Tests\Unit\Services\Schedule;

use Exception;
use PHPUnit\Framework\TestCase;
use Src\Enums\DestinationsEnum;
use Src\Enums\RouteTypesEnum;
use Src\Services\Schedule\DailyRouteGenerator;
use Src\Services\Schedule\DailyRouteTypesGenerator;
use Src\Services\Schedule\RandomRoutePicker;
use Src\Services\Routing\Collections\RouteCollection;
use Src\Services\Routing\Collections\RouteDestinationCollection;

class DailyRouteGeneratorTest extends TestCase
{
    /**
     *
     * @throws Exception
     */
    public function testGenerateRoutesProducesValidCollection(): void
    {
        $daysCount = 3;

        $routeTypesGenerator = $this->getMockBuilder(DailyRouteTypesGenerator::class)
            ->onlyMethods(['generateSchedule'])
            ->getMock();

        $routeTypesGenerator->method('generateSchedule')->willReturn([
            RouteTypesEnum::VIL,
            RouteTypesEnum::LOZ,
            RouteTypesEnum::BLY,
        ]);

        $generator = new DailyRouteGenerator($routeTypesGenerator);

        $routeCollection = $generator->generateRoutes($daysCount);

        $this->assertInstanceOf(RouteCollection::class, $routeCollection);
        $this->assertSame($daysCount, $routeCollection->getRoutesCount());

        foreach ($routeCollection as $route) {
            $this->assertInstanceOf(RouteDestinationCollection::class, $route);
            foreach ($route as $destination) {
                $this->assertInstanceOf(DestinationsEnum::class, $destination);
            }
        }
    }

    /**
     *
     * @throws Exception
     */
    public function testRandomRoutePickerReturnsRouteWithoutPrevious(): void
    {
        $routeType = RouteTypesEnum::VIL;
        $picker = new RandomRoutePicker($routeType);

        $route = $picker->getNextRoute(null);

        $this->assertInstanceOf(RouteDestinationCollection::class, $route);

        foreach ($route as $destination) {
            $this->assertInstanceOf(DestinationsEnum::class, $destination);
        }
    }
}

