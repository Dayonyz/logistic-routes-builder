<?php

namespace Tests\Unit\Services\Routing\Builders;

use PHPUnit\Framework\TestCase;
use Src\Enums\DestinationsEnum;
use Src\Services\Routing\Builders\VilRouteBuilder;
use Src\Services\Routing\Collections\RouteDestinationCollection;

class VilRouteBuilderTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testBuildsAllVillageCombinations(): void
    {
        $builder = new VilRouteBuilder();
        $routes = $builder->getCollectionWithAllPossibleRoutes();

        $villages = DestinationsEnum::getVillageDestinations();
        $expectedCount = count($villages) * (count($villages) - 1);

        $this->assertCount($expectedCount, $routes);

        foreach ($routes as $route) {
            /** @var RouteDestinationCollection $route */
            $destinations = $route->toArray();

            $this->assertCount(6, $destinations);

            $this->assertSame(DestinationsEnum::ZLP, $destinations[0]);
            $this->assertSame(DestinationsEnum::ZLP, $destinations[1]);

            $firstVillage  = $destinations[2];
            $secondVillage = $destinations[3];

            $this->assertContains($firstVillage, $villages);
            $this->assertContains($secondVillage, $villages);

            $this->assertNotSame(
                $firstVillage,
                $secondVillage,
                'Villages should not be repeated'
            );

            $this->assertSame(DestinationsEnum::ZLP, $destinations[4]);
            $this->assertSame(DestinationsEnum::ZLP, $destinations[5]);
        }
    }
}

