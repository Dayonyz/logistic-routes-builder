<?php

namespace Tests\Unit\Services\Routing\Collections;

use PHPUnit\Framework\TestCase;
use Src\Enums\DestinationsEnum;
use Src\Enums\RouteTypesEnum;
use Src\Services\Routing\Collections\RouteCollection;
use Src\Services\Routing\Collections\RouteDestinationCollection;
use Exception;

class RouteCollectionsTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testRouteDestinationCollectionLOZ(): void
    {
        $route = new RouteDestinationCollection();
        $route->add(DestinationsEnum::LOZ);

        $this->assertSame(RouteTypesEnum::LOZ, $route->getType());
        $this->assertStringContainsString('Lozova', $route->getRouteTitle());
    }

    /**
     * @throws Exception
     */
    public function testRouteDestinationCollectionBLY(): void
    {
        $route = new RouteDestinationCollection();
        $route->add(DestinationsEnum::BLY);

        $this->assertSame(RouteTypesEnum::BLY, $route->getType());
        $this->assertStringContainsString('Blyzniuky', $route->getRouteTitle());
    }

    /**
     * @throws Exception
     */
    public function testRouteDestinationCollectionVillage(): void
    {
        $route = new RouteDestinationCollection();
        $route->add(DestinationsEnum::ZLP, DestinationsEnum::BLV);

        $this->assertSame(RouteTypesEnum::VIL, $route->getType());
        $this->assertStringContainsString('Zlatopil', $route->getRouteTitle());
        $this->assertStringContainsString('Belyaivka', $route->getRouteTitle());
    }

    public function testRouteCollectionWithSingleType(): void
    {
        $route1 = new RouteDestinationCollection();
        $route1->add(DestinationsEnum::LOZ);

        $route2 = new RouteDestinationCollection();
        $route2->add(DestinationsEnum::LOZ);

        $collection = new RouteCollection();
        $collection->add($route1, $route2);

        $this->assertSame(RouteTypesEnum::LOZ, $collection->getType());
        $this->assertTrue($collection->isTyped());
    }

    /**
     * @throws Exception
     */
    public function testRouteCollectionWithDifferentTypesReturnsNull(): void
    {
        $routeLoz = new RouteDestinationCollection();
        $routeLoz->add(DestinationsEnum::LOZ);

        $routeBly = new RouteDestinationCollection();
        $routeBly->add(DestinationsEnum::BLY);

        $collection = new RouteCollection();
        $collection->add($routeLoz, $routeBly);

        $this->assertNull($collection->getType());
        $this->assertFalse($collection->isTyped());
    }



    public function testMixedRouteThrowsExceptionInDestinationCollection(): void
    {
        $route = new RouteDestinationCollection();
        $route->add(DestinationsEnum::LOZ, DestinationsEnum::BLY);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to determine route type');

        $route->getType();
    }
}

