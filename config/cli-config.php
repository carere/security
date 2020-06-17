<?php

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Tests\ApplicationContainer;

return ConsoleRunner::createHelperSet(
    ApplicationContainer::getContainerAndBootEnv()->get(
        EntityManagerInterface::class
    )
);
