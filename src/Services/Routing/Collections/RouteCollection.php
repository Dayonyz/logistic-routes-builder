<?php

namespace Src\Services\Routing\Collections;

use ArrayIterator;
use Exception;
use IteratorAggregate;
use Src\Enums\RouteTypesEnum;
use Traversable;

class RouteCollection implements IteratorAggregate
{
    /** @var RouteDestinationCollection[] $items */
    private array $items = [];
    private array $routeTypeCounters = [];

    public function __construct()
    {
        $this->initializeTypeCounters();
    }

    /**
     * @throws Exception
     */
    public function add(RouteDestinationCollection ...$items): void
    {
        foreach ($items as $item) {
            $this->items[$item->getRouteId()] = $item;
            $this->incRouteTypeCounters($item);
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
        $routeType = $collection->getRouteType()->value;
        ++$this->routeTypeCounters[$routeType];
    }

    /** @return Traversable<RouteDestinationCollection> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /** @return RouteDestinationCollection[] */
    public function toArray(): array
    {
        return $this->items;
    }

    public function getRoutesCount(): int
    {
        return count($this->items);
    }

    public function getRouteType(): ?RouteTypesEnum
    {
        $routeTypeMarker = array_flip(array_filter($this->routeTypeCounters, static fn($item) => $item > 0));

        return match (count($routeTypeMarker)) {
            1 => RouteTypesEnum::tryFrom(array_shift($routeTypeMarker)),
            default => null,
        };
    }

    public function isTyped(): bool
    {
        return ! is_null($this->getRouteType());
    }

    /**
     * @throws Exception
     */
    public function getRouteById(string $id): RouteDestinationCollection
    {
        if (! isset($this->items[$id])) {
            throw new Exception('Route not found with ID: ' . $id);
        }

        return $this->items[$id];
    }
}