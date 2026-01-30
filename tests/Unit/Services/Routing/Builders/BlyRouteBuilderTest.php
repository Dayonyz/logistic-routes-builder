<?php

namespace Tests\Unit\Services\Routing\Builders;

use Exception;
use PHPUnit\Framework\TestCase;
use Src\Enums\DestinationsEnum;
use Src\Services\Routing\Builders\BlyRouteBuilder;
use Src\Services\Routing\Collections\RouteDestinationCollection;

class BlyRouteBuilderTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testBuildsRoutesForAllVillages(): void
    {
        $builder = new BlyRouteBuilder();
        $routes = $builder->getCollectionWithAllPossibleRoutes();

        $villages = DestinationsEnum::getVillageDestinations();

        $this->assertCount(count($villages), $routes);

        foreach ($routes as $route) {
            /** @var RouteDestinationCollection $route */
            $destinations = $route->toArray();

            $this->assertSame(DestinationsEnum::ZLP, $destinations[0]);
            $this->assertSame(DestinationsEnum::ZLP, $destinations[1]);

            $this->assertContains(
                $destinations[2],
                $villages,
                'Third destination must be a village'
            );

            $this->assertSame(DestinationsEnum::BLY, $destinations[3]);

            $this->assertSame(DestinationsEnum::ZLP, $destinations[4]);
            $this->assertSame(DestinationsEnum::ZLP, $destinations[5]);
        }
    }
}