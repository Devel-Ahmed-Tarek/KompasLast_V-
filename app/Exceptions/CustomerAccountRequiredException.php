<?php

namespace App\Exceptions;

use RuntimeException;

class CustomerAccountRequiredException extends RuntimeException
{
    public function __construct(public readonly string $email)
    {
        parent::__construct('Login required to track this email');
    }
}
