<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class PluginException extends HttpException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct(422, $message, $previous);
    }
}
