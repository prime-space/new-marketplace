<?php namespace App\Api\Exception;

use Exception;

class ValidationException extends Exception
{
    private $errors;

    public function __construct(array $errors = [])
    {
        $this->errors = $errors;
        parent::__construct();
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
