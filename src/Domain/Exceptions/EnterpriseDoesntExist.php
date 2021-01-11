<?php

namespace Ashiso\Security\Domain\Exceptions;

class EnterpriseDoesntExist extends \Exception
{
    const MESSAGE = "The enterprise does not exist";

    public function __construct(
        string $message = "",
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message ?? self::MESSAGE, $code, $previous);
    }
}
