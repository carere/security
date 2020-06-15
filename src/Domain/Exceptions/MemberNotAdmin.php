<?php

namespace Addworking\Security\Domain\Exceptions;

class MemberNotAdmin extends \Exception
{
    const MESSAGE = "The member is not from Addworking";

    public function __construct(
        string $message = "",
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message ?? self::MESSAGE, $code, $previous);
    }
}
