<?php

namespace Src\Services\Routing\Collections;

use ArrayIterator;
use Exception;
use IteratorAggregate;
use Src\Enums\DestinationsEnum;
use Src\Enums\RouteTypesEnum;
use Traversable;

class RouteDestinationCollection implements IteratorAggregate
{
    /** @var DestinationsEnum[] */
    private array $items = [];
    private int $timeStamp;

    public function __construct()
    {
        $this->timeStamp = hrtime(true);
    }

    public function add(DestinationsEnum ...$items): void
    {
        foreach ($items as $item) {
            $this->items[] = $item;
        }
    }

    /** @return Traversable<DestinationsEnum> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /** @return DestinationsEnum[] */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @throws Exception
     */
    public function getRouteType(): RouteTypesEnum
    {
        $destinations = [];

        foreach ($this->toArray() as $destinationsEnum) {
            $destinations[$destinationsEnum->value] = $destinationsEnum;
        }

        ksort($destinations);

        $typeMarker = array_values(array_filter(
            $destinations,
            static fn(DestinationsEnum $item) => in_array($item, [DestinationsEnum::LOZ, DestinationsEnum::BLY])
        ));

        return match ($typeMarker) {
            [DestinationsEnum::LOZ] => RouteTypesEnum::LOZ,
            [DestinationsEnum::BLY] => RouteTypesEnum::BLY,
            [] => RouteTypesEnum::VIL,
            default => throw new Exception(
                'Unable to determine route type for destination collection: ' . $this->getRouteTitle()
            ),
        };
    }

    public function getRouteTitle(): string
    {
        return implode(' -> ', array_map(fn($item) => $item->title(), $this->items));
    }

    public function getRouteId(): string
    {
        return $this->timeStamp . '_' .implode('_', array_map(fn($item) => $item->value, $this->items));
    }
}