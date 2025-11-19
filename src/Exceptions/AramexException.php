<?php

namespace Shibin\Aramex\Exceptions;

use Exception;

class AramexException extends Exception
{
    public array $details = [];

    public function __construct(string $message, array $details = [], int $code = 0)
    {
        parent::__construct($message, $code);
        $this->details = $details;
    }
}
