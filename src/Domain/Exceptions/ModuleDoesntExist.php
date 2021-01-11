<?php

namespace Ashiso\Security\Domain\Exceptions;

class ModuleDoesntExist extends \Exception
{
    const MESSAGE = "The requested module doesn't exist";

    public function __construct(
        string $message = "",
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message ?? self::MESSAGE, $code, $previous);
    }
}
