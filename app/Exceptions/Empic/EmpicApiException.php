<?php

namespace App\Exceptions\Empic;

use RuntimeException;

class EmpicApiException extends RuntimeException
{
    public function __construct(string $message, public readonly array $context = [])
    {
        parent::__construct($message);
    }
}
