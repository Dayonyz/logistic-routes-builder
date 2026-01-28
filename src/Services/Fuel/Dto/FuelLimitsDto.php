<?php

namespace Src\Services\Fuel\Dto;

final class FuelLimitsDto
{
    public function __construct(
        public int $minFuelLiters,
        public int $maxFuelLiters,
    ) {}
}