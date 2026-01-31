<?php

namespace Src\Services\Routing;

use Exception;
use Src\Enums\RouteTypesEnum;
use Src\Services\Routing\Selectors\AboveAverageDistanceRouteSelector;
use Src\Services\Routing\Selectors\MaxDistanceCoveringAllDestinationsRouteSelector;
use Src\Services\Routing\Selectors\RouteSelector;

class RouteSelectorFactory
{
    private static ?self $instance = null;
    private RouteSelector $routeSelector;

    /**
     * @throws Exception
     */
    private function __construct(RouteTypesEnum $routeType)
    {
        $this->setRouteType($routeType);
    }

    private function __clone(): void
    {
        // Closed
    }

    /**
     * @throws Exception
     */
    public function setRouteType(RouteTypesEnum $routeType): void
    {
        $this->routeSelector = match ($routeType) {
            RouteTypesEnum::BLY,
            RouteTypesEnum::LOZ => new AboveAverageDistanceRouteSelector(),
            RouteTypesEnum::VIL => new MaxDistanceCoveringAllDestinationsRouteSelector(),
            default => throw new Exception('Undefined route type.'),
        };
    }

    /**
     * @throws Exception
     */
    public static function makeInstance(RouteTypesEnum $routeType): static
    {
        if (self::$instance === null) {
            self::$instance = new static($routeType);
        }

        self::$instance->setRouteType($routeType);

        return self::$instance;
    }

    public function getSelector(): RouteSelector
    {
        return $this->routeSelector;
    }
}

