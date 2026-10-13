<?php

declare(strict_types=1);

use App\Domain\Exceptions\CommissionException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {})
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(fn (Throwable $exception, Request $request): ?JsonResponse => match (true) {
            ! $request->is('api/*') => null,
            $exception instanceof ValidationException => api()->error($exception->getMessage(), 422, $exception->errors()),
            $exception instanceof CommissionException => api()->error($exception->getMessage(), 422),
            $exception instanceof HttpExceptionInterface => api()->error(
                $exception->getMessage() ?: Response::$statusTexts[$exception->getStatusCode()],
                $exception->getStatusCode(),
            ),
            default => null,
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
