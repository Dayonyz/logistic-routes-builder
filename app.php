<?php

use Src\Commands\RouteGeneratorCommand;
use Symfony\Component\Console\Application;

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$application = new Application($_ENV['APP_NAME']);
$application->add(new RouteGeneratorCommand());

try {
    $application->run();
} catch (Exception $e) {
    echo $e->getMessage() . "\r\n";
}

