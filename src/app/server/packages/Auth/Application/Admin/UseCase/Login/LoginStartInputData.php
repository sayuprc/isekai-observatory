<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Login;

readonly class LoginStartInputData
{
    public function __construct(public string $email)
    {
    }
}
