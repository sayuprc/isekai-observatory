<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Authenticate;

readonly class AuthenticateInputData
{
    public function __construct(public string $accessToken)
    {
    }
}
