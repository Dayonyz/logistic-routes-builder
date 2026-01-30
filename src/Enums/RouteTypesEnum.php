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

    /**
     * @return DestinationsEnum[]
     */
    public function getDestinations(): array
    {
        return match ($this) {
            self::VIL => array_filter(
                DestinationsEnum::cases(),
                fn($destination) => ! in_array($destination, [DestinationsEnum::LOZ, DestinationsEnum::BLY])
            ),
            self::LOZ => array_filter(
                DestinationsEnum::cases(),
                fn($destination) => $destination !== DestinationsEnum::BLY
            ),
            self::BLY => array_filter(
                DestinationsEnum::cases(),
                fn($destination) => $destination !== DestinationsEnum::LOZ
            ),
        };
    }

    public function getDestinationsCount(): int
    {
        return count($this->getDestinations());
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

