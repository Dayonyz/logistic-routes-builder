<?php

namespace Tests\Integration\Commands;

use PHPUnit\Framework\TestCase;
use Src\Commands\RouteGeneratorCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class RouteGeneratorCommandTest extends TestCase
{
    public function testRouteGeneratorCommandRunsSuccessfully(): void
    {
        $application = new Application();
        $application->add(new RouteGeneratorCommand(printReports: false));

        $command = $application->find('app:generate-routes');

        $tester = new CommandTester($command);

        $tester->setInputs([
            '27',   // days
            '275',  // fuel
            '65',   // good weather %
        ]);

        $exitCode = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $exitCode);
    }
}
