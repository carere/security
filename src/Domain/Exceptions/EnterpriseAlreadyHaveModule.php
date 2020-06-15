<?php

namespace Addworking\Security\Domain\Exceptions;

class EnterpriseAlreadyHaveModule extends \Exception
{
    const MESSAGE = "The enterprise already has the module";

    public function __construct(
        string $message = "",
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message ?? self::MESSAGE, $code, $previous);
    }
}
