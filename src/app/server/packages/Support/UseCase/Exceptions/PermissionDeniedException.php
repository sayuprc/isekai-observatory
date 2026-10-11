<?php

declare(strict_types=1);

namespace Support\UseCase\Exceptions;

class PermissionDeniedException extends UseCaseException
{
    public function __construct()
    {
        parent::__construct('権限がありません');
    }
}
