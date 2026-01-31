<?php

namespace Tests\Unit\Services\Schedule;

use DateInterval;
use Exception;
use Src\Services\Schedule\TimeRouteTracker;
use Src\Enums\DestinationsEnum;

class TestableTimeRouteTracker extends TimeRouteTracker
{
    public bool $isGoodWeather;
    public ?DestinationsEnum $from;
    public ?DestinationsEnum $to;
    public ?DestinationsEnum $previousFrom;
    public ?DestinationsEnum $previousTo;
    public ?\DateTimeImmutable $endMovingTime;
    public ?\DateTimeImmutable $previousEndMovingTime;

    /**
     * @throws Exception
     */
    public function generateStartInterval(): DateInterval
    {
        return parent::generateStartMovingInterval();
    }

    /**
     * @throws Exception
     */
    public function generateBetweenInterval(): DateInterval
    {
        return parent::generateBetweenMovingInterval();
    }

    /**
     * @throws Exception
     */
    public function getStartMinutes(): int
    {
        return parent::getStartMovingTimeMinutes();
    }

    /**
     * @throws Exception
     */
    public function getBetweenMinutes(): int
    {
        return parent::getBetweenMovingMinutes();
    }

    /**
     * @throws Exception
     */
    public function getViaBlyMinutes(): int
    {
        return parent::generateViaBlyExtraMinutes();
    }

    public function speedCorrection(string $type): int
    {
        return parent::getSpeedCorrection($type);
    }

    /**
     * @throws Exception
     */
    public function fluctuationCoefficient(): float
    {
        return parent::getFluctuationCoefficient();
    }

    /**
     * @throws Exception
     */
    public function movingMinutes(array $matrix): int
    {
        return parent::getMovingTimeMinutes($matrix);
    }

    /**
     * @throws Exception
     */
    public function applyFluct(int $minutes): int
    {
        return parent::applyFluctuation($minutes);
    }
}

