<?php

namespace Src\Services\Schedule;

use Exception;
use Src\Enums\DestinationsEnum;
use Src\Enums\RouteTypesEnum;
use Src\Services\Routing\Collections\RouteCollection;
use Src\Services\Routing\Collections\RouteDestinationCollection;
use Src\Services\Routing\RouteBuilderFactory;
use Src\Services\Routing\RouteSelectorFactory;

class RandomRoutePicker
{
    private RouteCollection $routeCollection;

    /**
     * @throws Exception
     */
    public function __construct(RouteTypesEnum $routeType)
    {
        $routeBuilderFactory = RouteBuilderFactory::makeInstance($routeType);
        $routeSelectorFactory = RouteSelectorFactory::makeInstance($routeType);
        $routeCollection = $routeBuilderFactory->getBuilder()->getCollectionWithAllPossibleRoutes();
        $this->routeCollection = $routeSelectorFactory->getSelector()->select($routeCollection);
    }

    /**
     * @throws Exception
     */
    public function getNextRoute(
        ?RouteDestinationCollection $prev = null,
        $scipRestrictions = false
    ): RouteDestinationCollection
    {
        if (is_null($prev)) {
            return $this->getRandomRoute();
        } else {
            $nextRoute = $this->getRandomRoute();

            if ($scipRestrictions) {
                return $nextRoute;
            }

            while ($this->routesHasIntersections($nextRoute, $prev)) {
                $nextRoute = $this->getRandomRoute();
            }

            return $nextRoute;
        }
    }

    /**
     * @throws Exception
     */
    protected function getRandomRoute(): RouteDestinationCollection
    {
        $index = random_int(0, count($this->routeCollection->toArray()) - 1);

        return array_values($this->routeCollection->toArray())[$index];
    }

    protected function routesHasIntersections(
        RouteDestinationCollection $first,
        RouteDestinationCollection $second
    ): bool {
        $firstRoute = array_filter($first->toArray(), static fn (DestinationsEnum $d) => $d !== DestinationsEnum::ZLP);
        $secondRoute = array_filter($second->toArray(), static fn (DestinationsEnum $d) => $d !== DestinationsEnum::ZLP);

        $firstKeys  = array_map(static fn (DestinationsEnum $d) => $d->value, $firstRoute);
        $secondKeys = array_map(static fn (DestinationsEnum $d) => $d->value, $secondRoute);

        return (bool) array_intersect($firstKeys, $secondKeys);
    }
}
