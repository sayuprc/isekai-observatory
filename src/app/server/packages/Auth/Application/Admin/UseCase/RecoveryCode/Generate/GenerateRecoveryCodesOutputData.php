<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\RecoveryCode\Generate;

readonly class GenerateRecoveryCodesOutputData
{
    /**
     * @param list<string> $plainCodes
     */
    public function __construct(public array $plainCodes)
    {
    }
}
