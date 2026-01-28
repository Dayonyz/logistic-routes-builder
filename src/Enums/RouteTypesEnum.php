<?php

namespace Src\Enums;

use Exception;

enum RouteTypesEnum: string
{
    case VIL = 'vil';
    case LOZ = 'loz';
    case BLY = 'bly';

    public function title(): string
    {
        return match ($this) {
            self::VIL => 'Route type - VILLAGES',
            self::LOZ => 'Route type - LOZOVA',
            self::BLY => 'Route type - BLYZNIUKY',

        };
    }

    public function getDestinationsCount(): int
    {
        return match ($this) {
            self::VIL => 16,
            self::LOZ,
            self::BLY => 17
        };
    }

    public function getOdds(): int
    {
        return match ($this) {
            self::VIL => 34,
            self::LOZ,
            self::BLY => 9
        };
    }

    public static function getOddsSum(): int
    {
        $sum = 0;

        foreach (self::cases() as $routeType) {
            $sum += $routeType->getOdds();
        }

        return $sum;
    }

    /**
     * @throws Exception
     */
    public static function getRandomType(): RouteTypesEnum
    {
        $choice = random_int(1, self::getOddsSum());
        $range = 0;

        foreach (self::cases() as $routeType) {
            $range += $routeType->getOdds();

            if ($choice <= $range) {
                return $routeType;
            }
        }

        throw new Exception('Incorrect odds sum for route types');
    }
}

