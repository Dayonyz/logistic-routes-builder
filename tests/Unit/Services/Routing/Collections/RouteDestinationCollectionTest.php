<?php

namespace Tests\Unit\Services\Routing\Collections;

use PHPUnit\Framework\TestCase;
use Src\Enums\DestinationsEnum;
use Src\Enums\RouteTypesEnum;
use Src\Services\Routing\Collections\RouteDestinationCollection;
use Exception;

class RouteDestinationCollectionTest extends TestCase
{
    public function testAddAndIteration(): void
    {
        $collection = new RouteDestinationCollection();
        $collection->add(
            DestinationsEnum::ZLP,
            DestinationsEnum::KRA,
            DestinationsEnum::ZLP
        );

        $items = iterator_to_array($collection);

        $this->assertSame(
            [DestinationsEnum::ZLP, DestinationsEnum::KRA, DestinationsEnum::ZLP],
            $items
        );
    }

    public function testToArrayReturnsSameItems(): void
    {
        $collection = new RouteDestinationCollection();
        $collection->add(DestinationsEnum::ZLP, DestinationsEnum::KRA);

        $this->assertSame(
            $collection->toArray(),
            iterator_to_array($collection)
        );
    }

    public function testRouteTypeVil(): void
    {
        $collection = new RouteDestinationCollection();
        $collection->add(DestinationsEnum::ZLP, DestinationsEnum::KRA);

        $this->assertSame(RouteTypesEnum::VIL, $collection->getType());
    }

    public function testRouteTypeLoz(): void
    {
        $collection = new RouteDestinationCollection();
        $collection->add(DestinationsEnum::ZLP, DestinationsEnum::LOZ);

        $this->assertSame(RouteTypesEnum::LOZ, $collection->getType());
    }

    public function testRouteTypeBly(): void
    {
        $collection = new RouteDestinationCollection();
        $collection->add(DestinationsEnum::ZLP, DestinationsEnum::BLY);

        $this->assertSame(RouteTypesEnum::BLY, $collection->getType());
    }

    public function testRouteTypeLozAndBlyThrowsException(): void
    {
        $this->expectException(Exception::class);

        $collection = new RouteDestinationCollection();
        $collection->add(
            DestinationsEnum::ZLP,
            DestinationsEnum::LOZ,
            DestinationsEnum::BLY
        );

        $collection->getType();
    }

    public function testRouteTitle(): void
    {
        $collection = new RouteDestinationCollection();
        $collection->add(
            DestinationsEnum::ZLP,
            DestinationsEnum::KRA,
            DestinationsEnum::ZLP
        );

        $this->assertSame(
            'Zlatopil -> Krasive -> Zlatopil',
            $collection->getRouteTitle()
        );
    }

    public function testRouteIdContainsDestinations(): void
    {
        $collection = new RouteDestinationCollection();
        $collection->add(DestinationsEnum::ZLP, DestinationsEnum::KRA);

        $id = $collection->getRouteId();

        $this->assertStringContainsString('zlp', $id);
        $this->assertStringContainsString('kra', $id);
    }
}

