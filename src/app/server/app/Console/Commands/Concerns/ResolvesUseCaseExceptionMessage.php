<?php

declare(strict_types=1);

namespace App\Console\Commands\Concerns;

use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\UseCase\Exceptions\UseCaseException;

/**
 * Console コマンドで既知の業務例外を運用者向けメッセージへ変換する
 *
 * CLI は契約境界を通らないため、VO 構築の InvalidDomainException も入力エラーとして扱う (ADR-0014)
 */
trait ResolvesUseCaseExceptionMessage
{
    private function resolveExceptionMessage(BusinessRuleViolationException|InvalidDomainException|UseCaseException $e): string
    {
        return $e->getMessage();
    }
}
