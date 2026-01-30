<?php

namespace Src\Services\Distance;

use JsonException;
use RuntimeException;
use Src\Enums\DestinationsEnum;

class DistanceCalculator
{
    protected array $matrix;

    /**
     * @throws JsonException|RuntimeException
     */
    public function __construct(?string $path = null)
    {
        if (is_null($path)) {
            $path = __DIR__ . '/matrix.json';
        }
        $this->matrix = self::loadJson($path);
    }

    protected function get(string $from, string $to): array
    {
        return $this->matrix[$from][$to]
            ?? throw new RuntimeException("Distance cell not found: {$from} -> {$to}");
    }

    public function getDistanceMatrixCellBetweenDestinations(
        DestinationsEnum $from,
        DestinationsEnum $to
    ): array {
        return $this->get($from->value, $to->value);
    }

    public function getDistanceBetweenDestinations(DestinationsEnum ...$destinations): int
    {
        if (count($destinations) < 2) {
            throw new RuntimeException('Need at least 2 destinations to calculate distance.');
        }

        $distance = 0;

        for ($i = 0; $i < count($destinations) - 1; $i++) {
            $distanceMatrixCell = $this->getDistanceMatrixCellBetweenDestinations($destinations[$i], $destinations[$i+1]);

            $distance += $distanceMatrixCell['s'];
        }

        return $distance;
    }

    /**
     * @throws JsonException|RuntimeException
     */
    private static function loadJson(string $path): array
    {
        if (!is_file($path)) {
            throw new RuntimeException("Matrix file not found: {$path}");
        }

        $json = file_get_contents($path);

        if ($json === false) {
            throw new RuntimeException("Cannot read matrix file: {$path}");
        }

        return json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    }
}

