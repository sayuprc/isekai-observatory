<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\UseCase\Exceptions\PermissionDeniedException;
use Support\UseCase\Exceptions\ResourceNotFoundException;
use Support\UseCase\Exceptions\UnauthenticatedException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * 例外クラス → {code, status} の対応表
 *
 * packages 層の例外を HTTP レスポンスへ変換する唯一の場所
 */
final class ApiExceptionRenderer
{
    /**
     * ルーティング由来の HTTP 例外 (404, 405 等) は契約外のため null を返し Laravel の既定に任せる
     */
    public static function render(Throwable $e): ?JsonResponse
    {
        if ($e instanceof HttpExceptionInterface) {
            return null;
        }

        [$payload, $status] = match (true) {
            $e instanceof UnauthenticatedException => ApiError::unauthenticated(),
            $e instanceof PermissionDeniedException => ApiError::permissionDenied(),
            $e instanceof ResourceNotFoundException => ApiError::notFound($e->getMessage()),
            $e instanceof BusinessRuleViolationException => ApiError::businessRuleViolation($e->getMessage()),
            default => ApiError::internalError(),
        };

        return response()->json($payload, $status);
    }
}
