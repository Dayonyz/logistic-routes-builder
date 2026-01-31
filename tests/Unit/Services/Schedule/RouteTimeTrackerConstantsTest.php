<?php

namespace Tests\Unit\Services\Schedule;

use Exception;
use PHPUnit\Framework\TestCase;
use Src\Services\Schedule\RouteTimeTracker;
use Src\Enums\IntervalUnitsEnum;

class RouteTimeTrackerConstantsTest extends TestCase
{
    public function testStartTimeConstants(): void
    {
        $this->assertSame('9:00', RouteTimeTracker::START_TIME_STR);
    }

    public function testPercentRand(): void
    {
        $this->assertSame(['from' => 1, 'to' => 100], RouteTimeTracker::PERCENT_RAND);
    }

    public function testStartTimeRand(): void
    {
        $this->assertSame(['from' => 0, 'to' => 9], RouteTimeTracker::START_TIME_RAND);
    }

    public function testBlyMovingRand(): void
    {
        $this->assertSame(['from' => 8, 'to' => 18], RouteTimeTracker::BLY_MOVING_RAND);
    }

    public function testBetweenMovingRand(): void
    {
        $this->assertSame(['from' => 4, 'to' => 13], RouteTimeTracker::BETWEEN_MOVING_RAND);
    }

    public function testGoodWeatherCoeff(): void
    {
        $this->assertSame(['plus' => 1.1, 'minus' => 0.95], RouteTimeTracker::GOOD_WEATHER_FL_COEFF);
    }

    public function testBadWeatherCoeff(): void
    {
        $this->assertSame(['plus' => 1.05, 'minus' => 0.90], RouteTimeTracker::BAD_WEATHER_FL_COEFF);
    }

    public function testRoadSpeed(): void
    {
        $this->assertSame(['g' => 95, 'n' => 40, 'b' => 30], RouteTimeTracker::ROAD_SPEED);
    }

    public function testRoadSpeedCorrection(): void
    {
        $expected = [
            'g' => ['plus' => 10, 'minus' => 25],
            'n' => ['plus' => 5, 'minus' => 10],
            'b' => ['plus' => 0, 'minus' => 5],
        ];

        $this->assertSame(RouteTimeTracker::ROAD_SPEED_CORRECTION, $expected);
    }

    /**
     * @throws Exception
     */
    public function testIntervalUnitsEnum(): void
    {
        $this->assertSame('M', IntervalUnitsEnum::UNIT->value);
        $this->assertSame('PT', IntervalUnitsEnum::PREFIX->value);
        $this->assertSame('5', IntervalUnitsEnum::MINUTE_STEP->value);
        $this->assertSame(5, IntervalUnitsEnum::MINUTE_STEP->toInt());
    }
}
