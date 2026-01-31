<?php

namespace Tests\Unit\Services\Routing;

use Exception;
use JetBrains\PhpStorm\ArrayShape;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Src\Enums\RouteTypesEnum;
use Src\Services\Routing\RouteBuilderFactory;
use Src\Services\Routing\Builders\BlyRouteBuilder;
use Src\Services\Routing\Builders\LozRouteBuilder;
use Src\Services\Routing\Builders\VilRouteBuilder;

class RouteBuilderFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(RouteBuilderFactory::class);
        $property = $reflection->getProperty('instance');
        $property->setValue(null);
    }

    /**
     * @dataProvider routeTypeToBuilderProvider
     * @throws Exception
     */
    public function testMakeInstanceCreatesProperBuilder(
        RouteTypesEnum $type,
        string $expectedBuilderClass
    ): void {
        $factory = RouteBuilderFactory::makeInstance($type);

        $this->assertInstanceOf(
            $expectedBuilderClass,
            $factory->getBuilder()
        );
    }

    #[ArrayShape(['BLY route' => "array", 'LOZ route' => "array", 'VIL route' => "array"])]
    public static function routeTypeToBuilderProvider(): array
    {
        return [
            'BLY route' => [RouteTypesEnum::BLY, BlyRouteBuilder::class],
            'LOZ route' => [RouteTypesEnum::LOZ, LozRouteBuilder::class],
            'VIL route' => [RouteTypesEnum::VIL, VilRouteBuilder::class],
        ];
    }

    /**
     * @throws Exception
     */
    public function testFactoryIsSingleton(): void
    {
        $factory1 = RouteBuilderFactory::makeInstance(RouteTypesEnum::BLY);
        $factory2 = RouteBuilderFactory::makeInstance(RouteTypesEnum::LOZ);

        $this->assertSame($factory1, $factory2);
    }

    /**
     * @throws Exception
     */
    public function testFactorySupportsAllRouteTypes(): void
    {
        $factory = RouteBuilderFactory::makeInstance(RouteTypesEnum::BLY);

        foreach (RouteTypesEnum::cases() as $type) {
            $factory->setRouteType($type);

            $builder = $factory->getBuilder();
            $this->assertNotNull($builder);

            match ($type) {
                RouteTypesEnum::BLY => $this->assertInstanceOf(BlyRouteBuilder::class, $builder),
                RouteTypesEnum::VIL => $this->assertInstanceOf(VilRouteBuilder::class, $builder),
                RouteTypesEnum::LOZ => $this->assertInstanceOf(LozRouteBuilder::class, $builder),
            };
        }
    }
}

