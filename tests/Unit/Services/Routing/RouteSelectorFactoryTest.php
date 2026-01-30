<?php

namespace Tests\Unit\Services\Routing;

use Exception;
use PHPUnit\Framework\TestCase;
use Src\Services\Routing\RouteSelectorFactory;
use Src\Services\Routing\Selectors\AboveAverageDistanceRouteSelector;
use Src\Services\Routing\Selectors\MaxDistanceCoveringAllDestinationsRouteSelector;
use Src\Enums\RouteTypesEnum;

class RouteSelectorFactoryTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testMakeInstanceReturnsSingleton(): void
    {
        $factory1 = RouteSelectorFactory::makeInstance(RouteTypesEnum::LOZ);
        $factory2 = RouteSelectorFactory::makeInstance(RouteTypesEnum::LOZ);

        $this->assertSame($factory1, $factory2, 'Factory should return same singleton instance');
    }

    /**
     * @throws Exception
     */
    public function testGetSelectorReturnsAboveAverageSelectorForLOZ(): void
    {
        $factory = RouteSelectorFactory::makeInstance(RouteTypesEnum::LOZ);
        $selector = $factory->getSelector();

        $this->assertInstanceOf(
            AboveAverageDistanceRouteSelector::class,
            $selector
        );
    }

    /**
     * @throws Exception
     */
    public function testGetSelectorReturnsAboveAverageSelectorForBLY(): void
    {
        $factory = RouteSelectorFactory::makeInstance(RouteTypesEnum::BLY);
        $selector = $factory->getSelector();

        $this->assertInstanceOf(
            AboveAverageDistanceRouteSelector::class,
            $selector
        );
    }

    /**
     * @throws Exception
     */
    public function testGetSelectorReturnsMaxDistanceSelectorForVIL(): void
    {
        $factory = RouteSelectorFactory::makeInstance(RouteTypesEnum::VIL);
        $selector = $factory->getSelector();

        $this->assertInstanceOf(
            MaxDistanceCoveringAllDestinationsRouteSelector::class,
            $selector
        );
    }
}

