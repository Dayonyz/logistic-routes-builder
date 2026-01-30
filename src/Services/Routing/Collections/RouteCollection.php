<?php

namespace Src\Services\Routing\Collections;

use ArrayIterator;
use Exception;
use IteratorAggregate;
use Src\Enums\RouteTypesEnum;
use Traversable;

class RouteCollection implements IteratorAggregate
{
    /** @var RouteDestinationCollection[] $routes */
    private array $routes;
    private array $routeTypeCounters;

    public function __construct()
    {
        $this->routes = [];
        $this->initializeTypeCounters();
    }

    /**
     * @throws Exception
     */
    public function add(RouteDestinationCollection ...$destinationCollections): void
    {
        foreach ($destinationCollections as $destinationCollection) {
            $this->routes[$destinationCollection->getRouteId()] = $destinationCollection;
            $this->incRouteTypeCounters($destinationCollection);
        }
    }

    private function initializeTypeCounters(): void
    {
        $this->routeTypeCounters = [];

        foreach (RouteTypesEnum::cases() as $routeTypeEnum) {
            $this->routeTypeCounters[$routeTypeEnum->value] = 0;
        }
    }

    /**
     * @throws Exception
     */
    private function incRouteTypeCounters(RouteDestinationCollection $collection): void
    {
        $routeType = $collection->getType()->value;

        ++$this->routeTypeCounters[$routeType];
    }

    /** @return Traversable<RouteDestinationCollection> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->routes);
    }

    /** @return RouteDestinationCollection[] */
    public function toArray(): array
    {
        return $this->routes;
    }

    public function getRoutesCount(): int
    {
        return count($this->routes);
    }

    public function getType(): ?RouteTypesEnum
    {
        $routeTypeMarker = array_filter($this->routeTypeCounters, static fn($item) => $item > 0);

        return count($routeTypeMarker) !== 1 ? null : RouteTypesEnum::tryFrom(array_keys($routeTypeMarker)[0]);
    }

    public function isTyped(): bool
    {
        return ! is_null($this->getType());
    }

    /**
     * @throws Exception
     */
    public function getRouteById(string $id): RouteDestinationCollection
    {
        if (! isset($this->routes[$id])) {
            throw new Exception('Route not found with ID: ' . $id);
        }

        return $this->routes[$id];
    }
}