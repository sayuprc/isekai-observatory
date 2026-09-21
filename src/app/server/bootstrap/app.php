<?php

declare(strict_types=1);

use App\Http\Responses\ApiExceptionRenderer;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\UseCase\Exceptions\UseCaseException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: [__DIR__ . '/../routes/admin.php', __DIR__ . '/../routes/viewer.php'],
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: '',
    )
    ->withMiddleware(static function (Middleware $middleware) {
        $middleware->remove(ConvertEmptyStringsToNull::class);
    })
    ->withExceptions(static function (Exceptions $exceptions) {
        // API のみのアプリのため、Accept ヘッダに依存せず常に JSON で例外を返す
        $exceptions->shouldRenderJsonWhen(static fn (): bool => true);

        // 期待される業務例外は 4xx として描画されるためログには記録しない
        $exceptions->dontReport([
            UseCaseException::class,
            BusinessRuleViolationException::class,
        ]);

        // 例外 → {code, status} の対応表は ApiExceptionRenderer に集約する
        $exceptions->render(ApiExceptionRenderer::render(...));
    })->create();
