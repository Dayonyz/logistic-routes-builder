<?php

namespace Src\Services\Routing;

use Exception;
use Src\Enums\RouteTypesEnum;
use Src\Services\Routing\Builders\BlyRouteBuilder;
use Src\Services\Routing\Builders\LozRouteBuilder;
use Src\Services\Routing\Builders\VilRouteBuilder;
use Src\Services\Routing\Builders\RouteBuilder;

class RouteBuilderFactory
{
    private static ?self $instance = null;
    private RouteBuilder $routeCollectionBuilder;

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
        $this->routeCollectionBuilder = match ($routeType) {
            RouteTypesEnum::BLY => new BlyRouteBuilder(),
            RouteTypesEnum::LOZ => new LozRouteBuilder(),
            RouteTypesEnum::VIL => new VilRouteBuilder(),
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


    public function getBuilder(): RouteBuilder
    {
        return $this->routeCollectionBuilder;
    }
}
