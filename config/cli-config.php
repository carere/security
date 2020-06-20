<?php

use Tests\Application;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

Application::initEnv();

return ConsoleRunner::createHelperSet(
    Application::getContainer()->get(EntityManagerInterface::class)
);
