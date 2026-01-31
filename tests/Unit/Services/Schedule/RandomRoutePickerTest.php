<?php

namespace Tests\Unit\Services\Schedule;

use Exception;
use PHPUnit\Framework\TestCase;
use Src\Enums\DestinationsEnum;
use Src\Enums\RouteTypesEnum;
use Src\Services\Schedule\RandomRoutePicker;
use Src\Services\Routing\Collections\RouteDestinationCollection;

class RandomRoutePickerTest extends TestCase
{
    /**
     * We check that the constructor creates an object and a collection of routes
     *
     * @throws Exception
     */
    public function testConstructorInitializesRoutes(): void
    {
        $picker = new RandomRoutePicker(RouteTypesEnum::VIL);

        $this->assertInstanceOf(RandomRoutePicker::class, $picker);

        $route = $picker->getNextRoute();
        $this->assertInstanceOf(RouteDestinationCollection::class, $route);
        $this->assertNotEmpty($route->toArray());

        foreach ($route->toArray() as $destination) {
            $this->assertInstanceOf(DestinationsEnum::class, $destination);
        }
    }

    /**
     * We check that getNextRoute avoids intersections with the previous route.
     *
     * @throws Exception
     */
    public function testGetNextRouteAvoidsIntersections(): void
    {
        $picker = new RandomRoutePicker(RouteTypesEnum::VIL);

        $prevRoute = $picker->getNextRoute();

        $nextRoute = $picker->getNextRoute($prevRoute);

        $prevDestinations = array_filter(
            array_map(fn($d) => $d->value, $prevRoute->toArray()),
            fn($v) => $v !== DestinationsEnum::ZLP->value
        );
        $nextDestinations = array_filter(
            array_map(fn($d) => $d->value, $nextRoute->toArray()),
            fn($v) => $v !== DestinationsEnum::ZLP->value
        );

        $intersect = array_intersect($prevDestinations, $nextDestinations);

        $this->assertEmpty($intersect, 'The next route intersects with the previous one');
    }

    /**
     * @throws Exception
     */
    public function testRoutesHasIntersectionsIsCalledAndReturnsTrue(): void
    {
        /**
         * previous route: [MYR]
         */
        $prev = new RouteDestinationCollection();
        $prev->add(DestinationsEnum::MYR);

        /**
         * 1-st route -> Intersection [MYR]
         * 2-nd route -> Without Intersection [KRA]
         */
        $routeWithIntersection = new RouteDestinationCollection();
        $routeWithIntersection->add(DestinationsEnum::MYR);

        $routeWithoutIntersection = new RouteDestinationCollection();
        $routeWithoutIntersection->add(DestinationsEnum::KRA);

        /**
         * Spy-class
         */
        $picker = new class(
            RouteTypesEnum::VIL,
            [$routeWithIntersection, $routeWithoutIntersection]
        ) extends RandomRoutePicker {

            private array $routes;
            private int $i = 0;

            public bool $routesHasIntersectionsCalled = false;
            public bool $routesHasIntersectionsReturnedTrue = false;

            public function __construct($type, array $routes)
            {
                $this->routes = $routes;
            }

            protected function getRandomRoute(): RouteDestinationCollection
            {
                return $this->routes[$this->i++];
            }

            protected function routesHasIntersections(
                RouteDestinationCollection $first,
                RouteDestinationCollection $second
            ): bool {
                $this->routesHasIntersectionsCalled = true;

                $result = parent::routesHasIntersections($first, $second);

                if ($result === true) {
                    $this->routesHasIntersectionsReturnedTrue = true;
                }

                return $result;
            }
        };

        /**
         * ACT
         */
        $next = $picker->getNextRoute($prev);

        /**
         * ASSERT — СУТЬ ТЕСТА
         */
        $this->assertTrue(
            $picker->routesHasIntersectionsCalled,
            'The routesHasIntersections method was not called'
        );

        $this->assertTrue(
            $picker->routesHasIntersectionsReturnedTrue,
            'The routesHasIntersections method returned false'
        );

        /**
         * Финальный маршрут НЕ должен пересекаться
         */
        $this->assertEquals(
            [DestinationsEnum::KRA],
            $next->toArray()
        );
    }
}
