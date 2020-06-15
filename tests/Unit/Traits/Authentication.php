<?php

namespace Tests\Unit\Traits;

trait Authentication
{
    private function authenticateUser(string $name)
    {
        $this->authenticationGateway->authenticate(
            $this->userRepository->findByName($name)
        );
    }
}
