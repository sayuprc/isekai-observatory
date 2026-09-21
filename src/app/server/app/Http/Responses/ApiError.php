<?php

declare(strict_types=1);

namespace App\Http\Responses;

use OpenAPI\Admin\Client\Model\ErrorCode;
use OpenAPI\Admin\Client\Model\ErrorDetail;
use OpenAPI\Admin\Client\Model\ErrorResponse;

/**
 * API エラーレスポンス {code, message, details?} の唯一の組み立て役
 *
 * ステータスとペイロードの組を返し、レスポンス化は呼び出し側で行う
 */
final class ApiError
{
    /**
     * @return array{0: ErrorResponse, 1: int}
     */
    public static function unauthenticated(): array
    {
        return [
            new ErrorResponse()
                ->setCode(ErrorCode::UNAUTHENTICATED)
                ->setMessage('認証が必要です'),
            401,
        ];
    }

    /**
     * @return array{0: ErrorResponse, 1: int}
     */
    public static function permissionDenied(): array
    {
        return [
            new ErrorResponse()
                ->setCode(ErrorCode::PERMISSION_DENIED)
                ->setMessage('権限がありません'),
            403,
        ];
    }

    /**
     * @return array{0: ErrorResponse, 1: int}
     */
    public static function notFound(string $message): array
    {
        return [
            new ErrorResponse()
                ->setCode(ErrorCode::NOT_FOUND)
                ->setMessage($message),
            404,
        ];
    }

    /**
     * @param array<string, array<string>> $errors field => メッセージの一覧
     *
     * @return array{0: ErrorResponse, 1: int}
     */
    public static function validationFailed(array $errors): array
    {
        $details = [];

        foreach ($errors as $field => $messages) {
            foreach ($messages as $message) {
                $details[] = new ErrorDetail()
                    ->setField($field)
                    ->setMessage($message);
            }
        }

        return [
            new ErrorResponse()
                ->setCode(ErrorCode::VALIDATION_FAILED)
                ->setMessage('入力内容に誤りがあります')
                ->setDetails($details),
            422,
        ];
    }

    /**
     * @return array{0: ErrorResponse, 1: int}
     */
    public static function businessRuleViolation(string $message): array
    {
        return [
            new ErrorResponse()
                ->setCode(ErrorCode::BUSINESS_RULE_VIOLATION)
                ->setMessage($message),
            400,
        ];
    }

    /**
     * @return array{0: ErrorResponse, 1: int}
     */
    public static function internalError(): array
    {
        return [
            new ErrorResponse()
                ->setCode(ErrorCode::INTERNAL_ERROR)
                ->setMessage('予期しないエラーが発生しました'),
            500,
        ];
    }
}
