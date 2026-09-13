<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null; // пусть Laravel обрабатывает web-запросы как обычно
            }

            return match (true) {
                $e instanceof ValidationException => response()->json([
                    'success' => false,
                    'message' => 'Ошибка валидации.',
                    'errors' => $e->errors(),
                ], 422),

                $e instanceof AuthenticationException => response()->json([
                    'success' => false,
                    'message' => 'Не аутентифицирован.',
                    'errors' => null,
                ], 401),

                $e instanceof AuthorizationException => response()->json([
                    'success' => false,
                    'message' => 'Доступ запрещён.',
                    'errors' => null,
                ], 403),

                $e instanceof ModelNotFoundException,
                $e instanceof NotFoundHttpException => response()->json([
                    'success' => false,
                    'message' => 'Ресурс не найден.',
                    'errors' => null,
                ], 404),

                $e instanceof MethodNotAllowedHttpException => response()->json([
                    'success' => false,
                    'message' => 'Метод не разрешён.',
                    'errors' => null,
                ], 405),

                default => null, // пусть стандартный обработчик решит (500 и т.п.)
            };
        });

        $exceptions->shouldRenderJsonWhen(function ($request, $e) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })->create();
