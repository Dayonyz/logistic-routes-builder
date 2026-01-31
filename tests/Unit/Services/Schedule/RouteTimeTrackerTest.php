<?php

namespace Tests\Unit\Services\Schedule;

use DateInterval;
use PHPUnit\Framework\TestCase;
use Src\Enums\DestinationsEnum;
use DateTimeImmutable;
use Exception;
use Src\Enums\IntervalUnitsEnum;
use Src\Services\Schedule\RouteTimeTracker;

class RouteTimeTrackerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testResetToDefaultsResetsInternalState(): void
    {
        $tracker = new TestableRouteTimeTracker(100);

        $tracker->setDestinations(
            DestinationsEnum::KRA,
            DestinationsEnum::BLY
        );

        $tracker->calculateMovingTime([
            'g' => 95,
            'n' => 40,
            'b' => 30,
        ]);

        $this->assertNotNull($tracker->getEndMovingTime());

        $tracker->resetToDefaults(50);

        $this->assertNull($tracker->previousEndMovingTime);
        $this->assertNull($tracker->previousFrom);
        $this->assertNull($tracker->previousTo);
        $this->assertNull($tracker->endMovingTime);
        $this->assertNull($tracker->from);
        $this->assertNull($tracker->to);

        $start = $tracker->getStartMovingTime();
        $this->assertInstanceOf(DateTimeImmutable::class, $start);

        $this->assertGreaterThanOrEqual(new DateTimeImmutable('9:00'), $start);
        $this->assertLessThanOrEqual(new DateTimeImmutable('9:45'), $start);
    }

    /**
     * @throws Exception
     */
    public function testTrackerCanBeReusedForMultipleDays(): void
    {
        $tracker = new TestableRouteTimeTracker(100);

        $legs = [
            DestinationsEnum::ZLP,
            DestinationsEnum::LOZ,
            DestinationsEnum::BLY,
        ];

        // DAY 1
        for ($i = 0; $i < count($legs) - 1; $i++) {
            $tracker->setDestinations($legs[$i], $legs[$i + 1]);

            $start = $tracker->getStartMovingTime();

            $tracker->calculateMovingTime([
                'g' => 95,
                'n' => 40,
                'b' => 30,
            ]);

            $end = $tracker->getEndMovingTime();

            $this->assertGreaterThan($start, $end);
        }

        $firstDayStart = $tracker->getStartMovingTime();

        // DAY 2 (reuse)
        $tracker->resetToDefaults(100);

        $secondDayStart = $tracker->getStartMovingTime();

        $this->assertInstanceOf(DateTimeImmutable::class, $secondDayStart);
        $this->assertGreaterThanOrEqual(new DateTimeImmutable('9:00'), $secondDayStart);
        $this->assertLessThanOrEqual(new DateTimeImmutable('9:45'), $secondDayStart);

        $this->assertNotSame($firstDayStart, $secondDayStart);
    }


    /** @throws Exception */
    public function testWeatherPercentValidation(): void
    {
        foreach ([1, 50, 100] as $p) {
            $this->assertInstanceOf(
                TestableRouteTimeTracker::class,
                new TestableRouteTimeTracker($p)
            );
        }

        foreach ([0, -1, 101] as $p) {
            $this->expectException(Exception::class);
            new TestableRouteTimeTracker($p);
        }
    }

    /** @throws Exception */
    public function testStartMovingTimeRange(): void
    {
        $t = new TestableRouteTimeTracker(50);
        $time = $t->getStartMovingTime();

        $this->assertGreaterThanOrEqual(new DateTimeImmutable('9:00'), $time);
        $this->assertLessThanOrEqual(new DateTimeImmutable('9:45'), $time);
    }

    /** @throws Exception */
    public function testStartIntervalMinutesRange(): void
    {
        $t = new TestableRouteTimeTracker(50);

        for ($i = 0; $i < 50; $i++) {
            $m = $t->getStartMinutes();
            $this->assertGreaterThanOrEqual(0, $m);
            $this->assertLessThanOrEqual(45, $m);
            $this->assertSame(0, $m % 5);
        }
    }

    /** @throws Exception */
    public function testBetweenIntervalRange(): void
    {
        $t = new TestableRouteTimeTracker(50);

        for ($i = 0; $i < 50; $i++) {
            $m = $t->getBetweenMinutes();
            $this->assertGreaterThanOrEqual(20, $m);
            $this->assertLessThanOrEqual(65, $m);
            $this->assertSame(0, $m % 5);
        }
    }

    /** @throws Exception */
    public function testViaBlyExtraMinutes(): void
    {
        $t = new TestableRouteTimeTracker(50);

        for ($i = 0; $i < 50; $i++) {
            $m = $t->getViaBlyMinutes();
            $this->assertGreaterThanOrEqual(40, $m);
            $this->assertLessThanOrEqual(90, $m);
            $this->assertSame(0, $m % 5);
        }
    }

    /** @throws Exception */
    public function testSpeedCorrection(): void
    {
        $tracker = new TestableRouteTimeTracker(100);
        $tracker->isGoodWeather = true;

        $this->assertSame(10, $tracker->speedCorrection('g'));
        $this->assertSame(5,  $tracker->speedCorrection('n'));
        $this->assertSame(0,  $tracker->speedCorrection('b'));

        $tracker->isGoodWeather = false;

        $this->assertSame(-25, $tracker->speedCorrection('g'));
        $this->assertSame(-10, $tracker->speedCorrection('n'));
        $this->assertSame(-5,  $tracker->speedCorrection('b'));
    }

    /** @throws Exception */
    public function testFluctuationCoefficient(): void
    {
        $t = new TestableRouteTimeTracker(100);
        $t->isGoodWeather = true;
        $t->setDestinations(DestinationsEnum::LOZ, DestinationsEnum::BLY);

        for ($i = 0; $i < 20; $i++) {
            $coef = $t->fluctuationCoefficient();
            $this->assertContains($coef, [1.1, 0.95]);
        }
    }

    /** @throws Exception */
    public function testMovingTimeCalculation(): void
    {
        $t = new TestableRouteTimeTracker(100);
        $t->setDestinations(DestinationsEnum::KRA, DestinationsEnum::BLY);

        $minutes = $t->movingMinutes([
            'g' => 95,
            'n' => 40,
            'b' => 30,
        ]);

        $this->assertGreaterThan(0, $minutes);
        $this->assertSame(0, $minutes % 5);
    }

    /** @throws Exception */
    public function testFullCycleLikeDailyRouteBuilder(): void
    {
        $t = new TestableRouteTimeTracker(100);

        $legs = [
            DestinationsEnum::ZLP,
            DestinationsEnum::LOZ,
            DestinationsEnum::BLY,
        ];

        for ($i = 0; $i < count($legs) - 1; $i++) {
            $t->setDestinations($legs[$i], $legs[$i + 1]);

            $start = $t->getStartMovingTime();

            $t->calculateMovingTime([
                'g' => 95,
                'n' => 40,
                'b' => 30,
            ]);

            $end = $t->getEndMovingTime();

            $this->assertGreaterThan($start, $end);
        }
    }

    /**
     * @throws Exception
     */
    public function testGenerateStartInterval(): void
    {
        $tracker = new TestableRouteTimeTracker(100);

        $interval = $tracker->generateStartInterval();

        $this->assertInstanceOf(DateInterval::class, $interval);

        $minutes = $interval->h * 60 + $interval->i;

        $this->assertSame(0, $interval->h);
        $this->assertGreaterThanOrEqual(
            IntervalUnitsEnum::MINUTE_STEP->toInt() * RouteTimeTracker::START_TIME_RAND['from'],
            $minutes
        );
        $this->assertLessThanOrEqual(
            IntervalUnitsEnum::MINUTE_STEP->toInt() * RouteTimeTracker::START_TIME_RAND['to'],
            $minutes
        );
    }

    /**
     * @throws Exception
     */
    public function testGenerateBetweenInterval(): void
    {
        $tracker = new TestableRouteTimeTracker(100);

        $interval = $tracker->generateBetweenInterval();

        $this->assertInstanceOf(DateInterval::class, $interval);

        $minutes = $interval->h * 60 + $interval->i;

        $this->assertGreaterThanOrEqual(
            IntervalUnitsEnum::MINUTE_STEP->toInt() * RouteTimeTracker::BETWEEN_MOVING_RAND['from'],
            $minutes
        );
        $this->assertLessThanOrEqual(
            IntervalUnitsEnum::MINUTE_STEP->toInt() * RouteTimeTracker::BETWEEN_MOVING_RAND['to'],
            $minutes
        );
    }

    /**
     * @throws Exception
     */
    public function testNotApplyFluctuationForZlp(): void
    {
        $tracker = new TestableRouteTimeTracker(100);
        $tracker->from = DestinationsEnum::ZLP;
        $tracker->to   = DestinationsEnum::ZLP;

        $result = $tracker->applyFluct(60);

        $this->assertSame(60, $result);
    }

    /**
     * @throws Exception
     */
    public function testApplyFluctAlwaysRoundedToStep(): void
    {
        $tracker = new TestableRouteTimeTracker(100);
        $tracker->from = DestinationsEnum::KRA;
        $tracker->to   = DestinationsEnum::BLY;

        $value = $tracker->applyFluct(73);

        $this->assertSame(
            0,
            $value % IntervalUnitsEnum::MINUTE_STEP->toInt(),
            'The result is always a multiple of the minute step'
        );
    }

    /**
     * @throws Exception
     */
    public function testApplyFluctuationWithAllWeatherCoefficients(): void
    {
        $baseMinutes = 60;
        $step = IntervalUnitsEnum::MINUTE_STEP->toInt();

        $coefficients = array_merge(
            RouteTimeTracker::GOOD_WEATHER_FL_COEFF,
            RouteTimeTracker::BAD_WEATHER_FL_COEFF
        );

        foreach ($coefficients as $label => $coeff) {
            $tracker = new DeterministicRouteTimeTracker(100, $coeff);
            $tracker->from = DestinationsEnum::KRA;
            $tracker->to   = DestinationsEnum::BLY;

            $result = $tracker->applyFluct($baseMinutes);

            $expected = (int)(
                round(($baseMinutes * $coeff) / $step) * $step
            );

            $this->assertSame(
                $expected,
                $result,
                "Fluctuation '{$label}' ({$coeff}) calculated incorrectly"
            );

            $this->assertSame(
                0,
                $result % $step,
                'Result must be rounded to minute step'
            );
        }
    }
}
