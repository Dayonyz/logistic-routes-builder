<?php

namespace Src\Services\Report\Dto;

use Src\Enums\DestinationsEnum;
use DateTimeImmutable;
use Src\Enums\RouteTypesEnum;

final class RouteLegDto
{
    public function __construct(
        public readonly RouteTypesEnum $routeType,
        public readonly string $name,
        public readonly DestinationsEnum $from,
        public readonly DestinationsEnum $to,
        public readonly DateTimeImmutable $departureAt,
        public readonly DateTimeImmutable $arrivalAt,
        public readonly int $distanceKm,
        public readonly float $fuelLiters,
    ) {}
}
