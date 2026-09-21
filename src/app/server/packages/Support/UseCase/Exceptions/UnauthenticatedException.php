<?php

declare(strict_types=1);

namespace Support\UseCase\Exceptions;

class UnauthenticatedException extends UseCaseException
{
    public function __construct()
    {
        parent::__construct('認証が必要です');
    }
}
