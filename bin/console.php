<?php

require_once __DIR__ . '/../vendor/autoload.php';

// allow script running unlimited time
set_time_limit(0);

use Symfony\Component\Console\Application;
use CheapRoute\Command\{
    RequestCommand, ParseCommand
};

$application  = new Application('Cheap Route', '0.0.1');

$application->addCommands([
    new RequestCommand(),
    new ParseCommand(),
]);

$application->run();
