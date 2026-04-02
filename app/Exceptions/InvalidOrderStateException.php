<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class InvalidOrderStateException extends RuntimeException
{
    public function __construct(string $message = 'Invalid order state transition')
    {
        parent::__construct($message);
    }
}
