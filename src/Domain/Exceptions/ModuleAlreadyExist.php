<?php

namespace Ashiso\Security\Domain\Exceptions;

class ModuleAlreadyExist extends \Exception
{
    const MESSAGE = "A module with the same name already exist";

    public function __construct(
        string $message = "",
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message ?? self::MESSAGE, $code, $previous);
    }
}
