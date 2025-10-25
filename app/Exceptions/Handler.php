<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        // Handle API routes specifically (baik expectsJson maupun routes /api/*)
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleJsonException($exception, $request);
        }

        return parent::render($request, $exception);
    }

    private function handleJsonException(Throwable $exception, $request = null)
    {
        // Handle Authentication Exception - INI YANG HARUS DIPERBAIKI
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please login first.',
                'data' => null
            ], 401);
        }

        // Handle Validation Errors
        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $exception->errors()
            ], 422);
        }

        // Handle JWT Exceptions
        if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
            return response()->json([
                'success' => false,
                'message' => 'Token is invalid',
                'data' => null
            ], 401);
        }
        
        if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
            return response()->json([
                'success' => false,
                'message' => 'Token has expired',
                'data' => null
            ], 401);
        }

        if ($exception instanceof \Tymon\JWTAuth\Exceptions\JWTException) {
            return response()->json([
                'success' => false,
                'message' => 'Token error',
                'data' => null
            ], 401);
        }

        // Handle Route Not Found
        if ($exception instanceof \Symfony\Component\Routing\Exception\RouteNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'data' => null
            ], 401);
        }

        // Handle Model Not Found
        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
                'data' => null
            ], 404);
        }

        // Handle Method Not Allowed
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed',
                'data' => null
            ], 405);
        }

        // Handle Not Found
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint not found',
                'data' => null
            ], 404);
        }

        // Handle Authorization Errors (Spatie Permission)
        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action',
                'data' => null
            ], 403);
        }

        // Default Server Error
        $statusCode = $this->getStatusCode($exception);
        $message = $this->getExceptionMessage($exception, $statusCode);

        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null
        ], $statusCode);
    }

    private function getStatusCode(Throwable $exception): int
    {
        // Check if it's an HTTP exception
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
            return $exception->getStatusCode();
        }
        if ($exception instanceof \Symfony\Component\Routing\Exception\RouteNotFoundException) {
            return 401;
        }
        return match (true) {
            $exception instanceof AuthenticationException => 401,
            $exception instanceof \Illuminate\Auth\Access\AuthorizationException => 403,
            $exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => 404,
            $exception instanceof ValidationException => 422,
            default => 500,
        };
    }

    private function getExceptionMessage(Throwable $exception, int $statusCode): string
    {
        if ($exception instanceof \Symfony\Component\Routing\Exception\RouteNotFoundException) {
            return 'Authentication required';
        }

        if (config('app.env') === 'production' && $statusCode === 500) {
            return 'Internal Server Error';
        }

        return $exception->getMessage() ?: 'An error occurred';
    }
}