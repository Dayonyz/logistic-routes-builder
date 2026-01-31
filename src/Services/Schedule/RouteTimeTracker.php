<?php

namespace Src\Services\Schedule;

use DateInterval;
use DateTimeImmutable;
use Exception;
use Src\Enums\DestinationsEnum;
use Src\Enums\IntervalUnitsEnum;

class RouteTimeTracker
{
    protected DateTimeImmutable $startMovingTime;
    protected ?DateTimeImmutable $endMovingTime;
    protected ?DateTimeImmutable $previousEndMovingTime;
    protected ?DestinationsEnum $previousTo;
    protected ?DestinationsEnum $previousFrom;
    protected bool $isGoodWeather;
    protected ?DestinationsEnum $from;
    protected ?DestinationsEnum $to;
    protected int $goodWeatherPercent;

    public const START_TIME_STR = '9:00';

    public const PERCENT_RAND = [
        'from' => 1,
        'to' => 100
    ];

    public const START_TIME_RAND = [
        'from' => 0,
        'to' => 9
    ];

    public const BLY_MOVING_RAND = [
        'from' => 8,
        'to' => 18
    ];

    public const BETWEEN_MOVING_RAND = [
        'from' => 4,
        'to' => 13
    ];

    public const GOOD_WEATHER_FL_COEFF = [
        'plus' => 1.1,
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
        $this->resetToDefaults($goodWeatherPercent);
    }

    /**
     * @throws Exception
     */
    public function resetToDefaults(int $goodWeatherPercent): void
    {
        $this->setGoodWeatherPercent($goodWeatherPercent);
        $this->generateWeather();

        $this->previousEndMovingTime = null;
        $this->previousFrom = null;
        $this->previousTo = null;
        $this->endMovingTime = null;
        $this->from = null;
        $this->to = null;

        $this->setStartMovingTime();
    }

    /**
     * @throws Exception
     */
    protected function setGoodWeatherPercent(int $goodWeatherPercent): void
    {
        if ($goodWeatherPercent < static::PERCENT_RAND['from'] || $goodWeatherPercent > static::PERCENT_RAND['to']) {
            throw new Exception('Good weather percents must be the value between 1 and 100.');
        }

        $this->goodWeatherPercent = $goodWeatherPercent;
    }

    /**
     * @throws Exception
     */
    protected function generateWeather(): void
    {
        $randomChoice = random_int(static::PERCENT_RAND['from'], static::PERCENT_RAND['to']);
        $this->isGoodWeather = $randomChoice <= $this->goodWeatherPercent;
    }

    public function getStartMovingTime(): DateTimeImmutable
    {
        return $this->startMovingTime;
    }

    /**
     * @throws Exception
     */
    public function setStartMovingTime(): void
    {
        if ($this->endMovingTime) {
            $this->startMovingTime = clone $this->endMovingTime->add($this->generateBetweenMovingInterval());
        } else {
            $this->startMovingTime = new DateTimeImmutable(static::START_TIME_STR);
            $this->startMovingTime = $this->startMovingTime->add($this->generateStartMovingInterval());
        }
    }

    protected function fixStartMovingTimeByPreviousMoving(): void
    {
        if (! $this->previousEndMovingTime && ! $this->previousFrom && ! $this->previousTo) {
            return;
        }

        if ($this->from === DestinationsEnum::ZLP && $this->to !== DestinationsEnum::ZLP) {
            $this->startMovingTime = clone $this->previousEndMovingTime;
        }

        if ($this->from === DestinationsEnum::ZLP &&
            $this->to === DestinationsEnum::ZLP &&
            $this->previousTo === DestinationsEnum::ZLP
        ) {
            $this->startMovingTime = clone $this->previousEndMovingTime;
        }

        if ($this->from === DestinationsEnum::LOZ &&
            $this->to === DestinationsEnum::LOZ &&
            $this->previousFrom !== DestinationsEnum::LOZ &&
            $this->previousTo === DestinationsEnum::LOZ
        ) {
            $this->startMovingTime = clone $this->previousEndMovingTime;
        }

        if ($this->from === DestinationsEnum::LOZ &&
            $this->to === DestinationsEnum::LOZ &&
            $this->previousFrom === DestinationsEnum::LOZ &&
            $this->previousTo === DestinationsEnum::LOZ
        ) {
            return;
        }

        if ($this->from === DestinationsEnum::LOZ &&
            $this->to !== DestinationsEnum::LOZ &&
            $this->previousFrom === DestinationsEnum::LOZ &&
            $this->previousTo === DestinationsEnum::LOZ
        ) {
            $this->startMovingTime = clone $this->previousEndMovingTime;
        }
    }

    public function getEndMovingTime(): ?DateTimeImmutable
    {
        return $this->endMovingTime;
    }

    /**
     * @throws Exception
     */
    public function setEndMovingTime(int $minutes): void
    {
        $this->endMovingTime = $this->startMovingTime->add(new DateInterval(
            IntervalUnitsEnum::PREFIX->value .
            $minutes .
            IntervalUnitsEnum::UNIT->value
        ));
    }

    /**
     * @throws Exception
     */
    protected function generateStartMovingInterval(): DateInterval
    {
        return new DateInterval(IntervalUnitsEnum::PREFIX->value .
            $this->getStartMovingTimeMinutes() .
            IntervalUnitsEnum::UNIT->value
        );
    }

    /**
     * @throws Exception
     */
    protected function getStartMovingTimeMinutes(): int
    {
        return IntervalUnitsEnum::MINUTE_STEP->toInt()*random_int(
            static::START_TIME_RAND['from'],
            static::START_TIME_RAND['to']
        );
    }

    /**
     * @throws Exception
     */
    protected function generateBetweenMovingInterval(): DateInterval
    {
        return new DateInterval(IntervalUnitsEnum::PREFIX->value .
            $this->getBetweenMovingMinutes() .
            IntervalUnitsEnum::UNIT->value
        );
    }

    /**
     * @throws Exception
     */
    protected function getBetweenMovingMinutes(): int
    {
        return IntervalUnitsEnum::MINUTE_STEP->toInt()*random_int(
            static::BETWEEN_MOVING_RAND['from'],
            static::BETWEEN_MOVING_RAND['to']
        );
    }

    /**
     * @throws Exception
     */
    protected function generateViaBlyExtraMinutes(): int
    {
        return IntervalUnitsEnum::MINUTE_STEP->value*random_int(
                static::BLY_MOVING_RAND['from'],
                static::BLY_MOVING_RAND['to']
            );
    }

    /**
     * @throws Exception
     */
    protected function applyFluctuation(int $movingTime): int
    {
        return (int)(round(
                $movingTime*$this->getFluctuationCoefficient() / IntervalUnitsEnum::MINUTE_STEP->toInt()) *
            IntervalUnitsEnum::MINUTE_STEP->toInt()
        );
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

        $randomChoice = random_int(static::PERCENT_RAND['from'], static::PERCENT_RAND['to']);

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
    protected function getMovingTimeMinutes(array $distanceMatrix): int
    {
        $movingTime = 0;

        foreach (self::ROAD_SPEED as $roadQuality => $speed) {
            $movingTime += $distanceMatrix[$roadQuality] / ($speed + $this->getSpeedCorrection($roadQuality));
        }

        return (int)round(
            $movingTime*60 / IntervalUnitsEnum::MINUTE_STEP->toInt()
        )*IntervalUnitsEnum::MINUTE_STEP->toInt();
    }

    /**
     * @throws Exception
     */
    public function calculateMovingTime(array $distanceMatrix): void
    {
        $movingTime = $this->getMovingTimeMinutes($distanceMatrix);

        $movingTime = $this->applyFluctuation($movingTime);

        if ($this->to === DestinationsEnum::BLY) {
            $movingTime += $this->generateViaBlyExtraMinutes();
        }

        $this->setEndMovingTime($movingTime);
    }

    /**
     * @throws Exception
     */
    public function setDestinations(DestinationsEnum $from, DestinationsEnum $to): void
    {
        $this->savePreviousMoving();

        $this->from = $from;
        $this->to = $to;

        $this->setStartMovingTime();
        $this->fixStartMovingTimeByPreviousMoving();
    }

    protected function savePreviousMoving(): void
    {
        if ($this->from && $this->to && $this->endMovingTime) {
            $this->previousEndMovingTime = clone $this->endMovingTime;
            $this->previousTo = $this->to;
            $this->previousFrom = $this->from;
        }
    }
}
