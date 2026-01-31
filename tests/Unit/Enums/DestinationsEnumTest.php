<?php

namespace Tests\Unit\Enums;

use PHPUnit\Framework\TestCase;
use Src\Enums\DestinationsEnum;

class DestinationsEnumTest extends TestCase
{
    public function testTitleIsNotEmpty(): void
    {
        foreach (DestinationsEnum::cases() as $case) {
            $this->assertNotEmpty(
                $case->title(),
                "Empty title for {$case->name}"
            );
        }
    }

    public function testIsVillage(): void
    {
        $this->assertFalse(DestinationsEnum::ZLP->isVillage());
        $this->assertFalse(DestinationsEnum::LOZ->isVillage());
        $this->assertFalse(DestinationsEnum::BLY->isVillage());

        $this->assertTrue(DestinationsEnum::OLE->isVillage());
        $this->assertTrue(DestinationsEnum::KRA->isVillage());
    }

    public function testGetVillageDestinationsContainsOnlyVillages(): void
    {
        $villages = DestinationsEnum::getVillageDestinations();

        $this->assertNotEmpty($villages);

        foreach ($villages as $destination) {
            $this->assertInstanceOf(DestinationsEnum::class, $destination);
            $this->assertTrue(
                $destination->isVillage(),
                "{$destination->name} is not a village"
            );
        }
    }

    public function testVillageDestinationsDoesNotContainCities(): void
    {
        $villages = DestinationsEnum::getVillageDestinations();

        $this->assertNotContains(DestinationsEnum::ZLP, $villages);
        $this->assertNotContains(DestinationsEnum::LOZ, $villages);
        $this->assertNotContains(DestinationsEnum::BLY, $villages);
    }
}
