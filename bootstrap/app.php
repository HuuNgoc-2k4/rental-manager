<?php

use App\Exceptions\BusinessException;
use App\Http\Middleware\CheckUserStatus;
use App\Http\Middleware\EnsurePasswordChanged;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo('/login');
        $middleware->alias([
            'check.status' => CheckUserStatus::class,
            'password.changed' => EnsurePasswordChanged::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Request JSON/AJAX: KHÔNG lộ message kỹ thuật thô ra response body; giữ message chủ đích cho user.
        $exceptions->render(function (Throwable $e, Request $request) {
            if (!$request->expectsJson()) {
                return null;
            }

            if ($e instanceof BusinessException) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'code' => $e->getErrorCode(),
                ], 422);
            }

            if ($e instanceof ValidationException || $e instanceof AuthenticationException) {
                return null;
            }

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $message = match ($status) {
                    404, 405 => 'Không tìm thấy chức năng. Vui lòng tải lại trang rồi thử lại.',
                    403 => 'Bạn không có quyền thực hiện thao tác này.',
                    419 => 'Phiên đã hết hạn. Vui lòng tải lại trang rồi thử lại.',
                    429 => 'Thao tác quá nhanh, vui lòng thử lại sau giây lát.',
                    default => 'Có lỗi xảy ra, vui lòng thử lại.'
                };

                return response()->json(['status' => 'error', 'message' => $message], $status);
            }

            if (!config('app.debug')) {
                return response()->json(['status' => 'error', 'message' => 'Có lỗi hệ thống, vui lòng thử lại sau.'], 500);
            }

            return null;
        });
    })->create();
