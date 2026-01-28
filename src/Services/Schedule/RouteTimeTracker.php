<?php

namespace Src\Services\Schedule;

use DateInterval;
use DateTimeImmutable;
use Exception;
use Src\Enums\DestinationsEnum;

class RouteTimeTracker
{
    private DateTimeImmutable $startMovingTime;
    private DateTimeImmutable $endMovingTime;
    private DateTimeImmutable $previousEndMovingTime;
    private bool $isGoodWeather;
    private DestinationsEnum $from;
    private DestinationsEnum $to;
    protected int $goodWeatherPercent;

    public const GOOD_WEATHER_FL_COEFF = [
        'plus' => 1.08,
        'minus' => 0.95
    ];
    public const BAD_WEATHER_FL_COEFF = [
        'plus' => 1.05,
        'minus' => 0.90
    ];
    public const GOOD_WEATHER_FL_ODDS = 75;
    public const BAD_WEATHER_FL_ODDS = 25;

    public const ROAD_SPEED = [
        'g' => 95,
        'n' => 40,
        'b' => 30
    ];

    public const ROAD_SPEED_CORRECTION = [
        'g' => [
            'plus' => 10,
            'minus' => 25
        ],
        'n' => [
            'plus' => 5,
            'minus' => 10
        ],
        'b' => [
            'plus' => 0,
            'minus' => 5
        ],
    ];

    /**
     * @throws Exception
     */
    public function __construct(int $goodWeatherPercent)
    {
        $this->setGoodWeatherPercent($goodWeatherPercent);
        $this->generateWeather();
        $this->startMovingTime = new DateTimeImmutable('9:00');
        $this->startMovingTime = $this->startMovingTime->add($this->generateStartMovingInterval());
        $this->previousEndMovingTime =  new DateTimeImmutable('00:00');
    }

    /**
     * @throws Exception
     */
    protected function setGoodWeatherPercent(int $goodWeatherPercent): void
    {
        if ($goodWeatherPercent < 1 || $goodWeatherPercent > 100) {
            throw new Exception('Good weather percents must be the value between 1 and 100.');
        }

        $this->goodWeatherPercent = $goodWeatherPercent;
    }

    public function getStartMovingTime(): DateTimeImmutable
    {
        if ($this->from === DestinationsEnum::LOZ && $this->to !== DestinationsEnum::LOZ) {
            $this->startMovingTime = clone $this->previousEndMovingTime;
        }

        return $this->startMovingTime;
    }

    public function getEndMovingTime(): DateTimeImmutable
    {
        return $this->endMovingTime;
    }

    /**
     * @throws Exception
     */
    protected function generateWeather(): void
    {
        $randomChoice = random_int(1, 100);
        $this->isGoodWeather = $randomChoice <= $this->goodWeatherPercent;
    }

    /**
     * @throws Exception
     */
    protected function generateStartMovingInterval(): DateInterval
    {
        return new DateInterval('PT' . 5*random_int(0, 9) . 'M');
    }

    /**
     * @throws Exception
     */
    protected function generateBetweenMovingInterval(): DateInterval
    {
        return new DateInterval('PT' . 5*random_int(4, 13) . 'M');
    }

    protected function getSpeedCorrection(string $roadType): int
    {
        if ($this->isGoodWeather) {
            return self::ROAD_SPEED_CORRECTION[$roadType]['plus'];
        } else {
            return -self::ROAD_SPEED_CORRECTION[$roadType]['minus'];
        }
    }

    /**
     * @throws Exception
     */
    protected function getFluctuationCoefficient(): float
    {
        if ($this->from === DestinationsEnum::ZLP && $this->to === DestinationsEnum::ZLP) {
            return 1;
        }

        $randomChoice = random_int(1, 100);

        $sign = $this->isGoodWeather ?
            ($randomChoice <= self::GOOD_WEATHER_FL_ODDS ? 'plus' : 'minus') :
            ($randomChoice <= self::BAD_WEATHER_FL_ODDS ? 'plus' : 'minus');

        return $this->isGoodWeather ?
            self::GOOD_WEATHER_FL_COEFF[$sign] :
            self::BAD_WEATHER_FL_COEFF[$sign];
    }

    /**
     * @throws Exception
     */
    public function calculateMovingTime(array $distanceMatrix): static
    {
        $movingTime = 0;

        foreach (self::ROAD_SPEED as $roadQuality => $speed) {
            $movingTime += $distanceMatrix[$roadQuality] / ($speed + $this->getSpeedCorrection($roadQuality));
        }

        $movingTime = (int)(round($movingTime*$this->getFluctuationCoefficient()*60 / 5) * 5);

        if ($this->to === DestinationsEnum::BLY) {
            $movingTime += 5*random_int(8, 18);
        }

        $this->endMovingTime = $this->startMovingTime->add(new DateInterval('PT' . $movingTime . 'M'));

        return $this;
    }

    public function setDestinations(DestinationsEnum $from, DestinationsEnum $to): void
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @throws Exception
     */
    public function generateNextStartMovingTime(): static
    {
        $this->previousEndMovingTime = clone $this->endMovingTime;

        if ($this->from === DestinationsEnum::ZLP && $this->to === DestinationsEnum::ZLP) {
            $this->startMovingTime = clone $this->endMovingTime;

            return $this;
        }

        if ($this->from !== DestinationsEnum::ZLP && $this->to === DestinationsEnum::ZLP) {
            $this->startMovingTime = clone $this->endMovingTime;

            return $this;
        }

        if ($this->from !== DestinationsEnum::LOZ && $this->to === DestinationsEnum::LOZ) {
            $this->startMovingTime = clone $this->endMovingTime;

            return $this;
        }

        $this->startMovingTime = clone $this->endMovingTime->add($this->generateBetweenMovingInterval());

        return $this;
    }
}