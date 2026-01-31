<?php

namespace Tests\Unit\Services\Schedule;

class DeterministicRouteTimeTracker extends TestableRouteTimeTracker
{
    private float $fluctuation;

    public function __construct(int $goodWeatherPercent, float $fluctuation)
    {
        parent::__construct($goodWeatherPercent);
        $this->fluctuation = $fluctuation;
    }

    protected function getFluctuationCoefficient(): float
    {
        return $this->fluctuation;
    }
}