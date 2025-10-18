<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException as LaravelValidationException;

class ValidationException extends Exception
{
    protected $errors;

    public function __construct(array $errors, string $message = 'Validation failed')
    {
        parent::__construct($message, 422);
        $this->errors = $errors;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'data' => null,
            'errors' => $this->errors
        ], $this->getCode());
    }

    public static function fromLaravelException(LaravelValidationException $e): self
    {
        return new self($e->errors(), $e->getMessage());
    }
}