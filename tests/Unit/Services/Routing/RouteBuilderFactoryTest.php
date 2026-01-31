<?php

namespace Tests\Unit\Services\Routing;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Src\Enums\RouteTypesEnum;
use Src\Services\Routing\RouteBuilderFactory;
use Src\Services\Routing\Builders\BlyRouteBuilder;
use Src\Services\Routing\Builders\LozRouteBuilder;
use Src\Services\Routing\Builders\VilRouteBuilder;
use ValueError;

class RouteBuilderFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        $reflection = new ReflectionClass(RouteBuilderFactory::class);
        $property = $reflection->getProperty('instance');
        $property->setValue(null);
    }

    /**
     * @throws Exception
     */
    public function testCreatesBlyRouteBuilder(): void
    {
        $factory = RouteBuilderFactory::makeInstance(RouteTypesEnum::BLY);

        $this->assertInstanceOf(
            BlyRouteBuilder::class,
            $factory->getBuilder()
        );
    }

    /**
     * @throws Exception
     */
    public function testCreatesLozRouteBuilder(): void
    {
        $factory = RouteBuilderFactory::makeInstance(RouteTypesEnum::LOZ);

        $this->assertInstanceOf(
            LozRouteBuilder::class,
            $factory->getBuilder()
        );
    }

    /**
     * @throws Exception
     */
    public function testCreatesVilRouteBuilder(): void
    {
        $factory = RouteBuilderFactory::makeInstance(RouteTypesEnum::VIL);

        $this->assertInstanceOf(
            VilRouteBuilder::class,
            $factory->getBuilder()
        );
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
    public function testSetRouteTypeChangesBuilder(): void
    {
        $factory = RouteBuilderFactory::makeInstance(RouteTypesEnum::BLY);

        $this->assertInstanceOf(BlyRouteBuilder::class, $factory->getBuilder());

        $factory->setRouteType(RouteTypesEnum::VIL);

        $this->assertInstanceOf(VilRouteBuilder::class, $factory->getBuilder());

        $factory->setRouteType(RouteTypesEnum::LOZ);

        $this->assertInstanceOf(LozRouteBuilder::class, $factory->getBuilder());
    }

    /**
     * @throws Exception
     */
    public function testThrowsExceptionForUndefinedRouteType(): void
    {
        $this->expectException(ValueError::class);

        RouteBuilderFactory::makeInstance(
            RouteTypesEnum::from(RouteTypesEnum::BLY->value . '3')
        );
    }
}

