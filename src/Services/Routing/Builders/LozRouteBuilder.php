<?php

namespace Src\Services\Routing\Builders;

use Exception;
use Src\Enums\DestinationsEnum;
use Src\Services\Routing\Collections\RouteCollection;
use Src\Services\Routing\Collections\RouteDestinationCollection;

class LozRouteBuilder implements RouteBuilder
{
    /**
     * @throws Exception
     */
    public function getCollectionWithAllPossibleRoutes(): RouteCollection
    {
        $routes = new RouteCollection();

        foreach (DestinationsEnum::getVillageDestinations() as $destination) {
            $destinations = new RouteDestinationCollection();

            $destinations->add(DestinationsEnum::ZLP, DestinationsEnum::ZLP);
            $destinations->add($destination);
            $destinations->add(DestinationsEnum::LOZ, DestinationsEnum::LOZ, DestinationsEnum::LOZ);
            $destinations->add(DestinationsEnum::ZLP, DestinationsEnum::ZLP);

            $routes->add($destinations);
        }

        return $routes;
    }
}
