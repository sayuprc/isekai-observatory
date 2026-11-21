<?php

declare(strict_types=1);

namespace Auth\Application\Admin\Authenticate;

use SensitiveParameter;

readonly class AuthenticateInputData
{
    public function __construct(#[SensitiveParameter] public string $accessToken)
    {
    }
}
