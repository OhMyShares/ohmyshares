<?php

namespace App\Exceptions;

use Exception;

class ImportException extends Exception
{
    public function __construct($message = "", protected $details = "")
    {
        parent::__construct($message);
    }

    public function setDetails($value): void
    {
        $this->details = $value;
    }

    public function getDetails()
    {
        return $this->details;
    }
}
