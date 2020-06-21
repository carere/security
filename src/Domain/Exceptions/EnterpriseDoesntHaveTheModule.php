<?php

namespace Addworking\Security\Domain\Exceptions;

class EnterpriseDoesntHaveTheModule extends \Exception
{
    const MESSAGE = "The enterprise doesn't have the module";

    public function __construct(
        string $message = "",
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message ?? self::MESSAGE, $code, $previous);
    }
}
