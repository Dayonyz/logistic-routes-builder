<?php

namespace Src\Services\Routing\Builders;

use Exception;
use Src\Enums\DestinationsEnum;
use Src\Services\Routing\Collections\RouteCollection;
use Src\Services\Routing\Collections\RouteDestinationCollection;

class VilRouteBuilder implements RouteBuilder
{
    /**
     * @throws Exception
     */
    public function getCollectionWithAllPossibleRoutes(): RouteCollection
    {
        $routes = new RouteCollection();

        /** @var DestinationsEnum[] $vilDestinations */
        foreach (arrangementsNoRepeat(DestinationsEnum::getVillageDestinations(), 2) as $vilDestinations) {
            $destinations = new RouteDestinationCollection();

            $destinations->add(DestinationsEnum::ZLP, DestinationsEnum::ZLP);
            $destinations->add(...$vilDestinations);

            $destinations->add(DestinationsEnum::ZLP, DestinationsEnum::ZLP);

            $routes->add($destinations);
        }

        return $routes;
    }
}
