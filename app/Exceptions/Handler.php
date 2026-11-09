<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    public function render($request, Throwable $exception)
    {
        $status  = 500;
        $message = 'Internal Server Error';
        $errors  = null;

        if ($exception instanceof ValidationException) {
            $status  = 400;
            $message = 'Bad Request';
            $errors  = $exception->errors();
        } elseif ($exception instanceof MethodNotAllowedHttpException) {
            $status  = 405;
            $message = 'Method Not Allowed';
        } elseif ($exception instanceof NotFoundHttpException
                  || $exception instanceof ModelNotFoundException) {
            $status  = 404;
            $message = 'Not Found';
        } elseif ($exception instanceof AuthorizationException) {
            $status  = 403;
            $message = 'Forbidden';
        } elseif ($exception instanceof HttpException) {
            $status  = $exception->getStatusCode();
            $message = $exception->getMessage() ?: 'HTTP Error';
        } elseif (env('APP_DEBUG', false)) {
            $message = $exception->getMessage();
        }

        $body = [
            'success' => false,
            'status'  => $status,
            'message' => $message,
        ];

        if ($errors) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status);
    }
}