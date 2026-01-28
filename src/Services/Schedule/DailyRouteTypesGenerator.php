<?php

namespace Src\Services\Schedule;

use Exception;
use Src\Enums\RouteTypesEnum;

class DailyRouteTypesGenerator
{
    private int $blyTypeMinCount;
    private int $lozTypeMinCount;
    private int $sumBlyAndLozTypesMinCount;
    private int $sumBlyAndLozTypesMaxCount;
    private array $routeTypesCounters;

    public function __construct()
    {
        $this->resetRouteTypesCounters();
    }

    private function resetRouteTypesCounters(): void {
        foreach (RouteTypesEnum::cases() as $routeType) {
            $this->routeTypesCounters[$routeType->value] = 0;
        }
    }

    private function setRouteLevels(int $daysCount): void
    {
        $this->blyTypeMinCount = (int)round(
            (RouteTypesEnum::BLY->getOdds()/RouteTypesEnum::getOddsSum())*$daysCount*0.7
        );

        $this->lozTypeMinCount = (int)round(
            (RouteTypesEnum::LOZ->getOdds()/RouteTypesEnum::getOddsSum())*$daysCount*0.7
        );

        $this->sumBlyAndLozTypesMinCount = round((RouteTypesEnum::BLY->getOdds()/RouteTypesEnum::getOddsSum()*$daysCount +
                RouteTypesEnum::LOZ->getOdds()/RouteTypesEnum::getOddsSum()*$daysCount)*0.88);

        $this->sumBlyAndLozTypesMaxCount = round((RouteTypesEnum::BLY->getOdds()/RouteTypesEnum::getOddsSum()*$daysCount +
                RouteTypesEnum::LOZ->getOdds()/RouteTypesEnum::getOddsSum()*$daysCount)*1.12);
    }

    public function generateSchedule(int $daysCount, $scipRestrictions = false): array
    {
        $this->setRouteLevels($daysCount);

        $routeTypes = [];

        while ($this->routeTypesCounters[RouteTypesEnum::BLY->value] < $this->blyTypeMinCount ||
            $this->routeTypesCounters[RouteTypesEnum::LOZ->value] < $this->lozTypeMinCount ||
            $this->routeTypesCounters[RouteTypesEnum::BLY->value] +
            $this->routeTypesCounters[RouteTypesEnum::LOZ->value] < $this->sumBlyAndLozTypesMinCount ||
            $this->routeTypesCounters[RouteTypesEnum::BLY->value] +
            $this->routeTypesCounters[RouteTypesEnum::LOZ->value] > $this->sumBlyAndLozTypesMaxCount
        ) {
            $this->resetRouteTypesCounters();

            $routeTypes = $this->generateDayTypes($daysCount);
        }

        if ($scipRestrictions) {
            return $routeTypes;
        }

        $repeatLozAndBly = true;

        while ($repeatLozAndBly) {
            for ($i = 0; $i < count($routeTypes) - 1; $i++) {
                if ($this->isRouteRepeated($routeTypes[$i], $routeTypes[$i + 1])) {
                    break;
                }
            }

            if ($i === $daysCount - 1) {
                $repeatLozAndBly = false;
            } else {
                shuffle($routeTypes);
            }
        }

        return $routeTypes;
    }

    protected function isRouteRepeated(RouteTypesEnum $current, RouteTypesEnum $next): bool
    {
        return ($current === RouteTypesEnum::LOZ && $next === RouteTypesEnum::LOZ) ||
            ($current === RouteTypesEnum::BLY && $next === RouteTypesEnum::BLY) ||
            ($current === RouteTypesEnum::LOZ && $next === RouteTypesEnum::BLY) ||
            ($current === RouteTypesEnum::BLY && $next === RouteTypesEnum::LOZ);
    }

    private function generateDayTypes(int $daysCount): array {
        $routeTypes = [];
        for ($i = 0; $i < $daysCount; $i++) {
            try {
                $routeType = RouteTypesEnum::getRandomType();
                $this->routeTypesCounters[$routeType->value] += 1;
                $routeTypes[] = $routeType;
            } catch (Exception $e) {
                echo $e->getMessage() . "\r\n";
            }
        }

        return $routeTypes;
    }
}
