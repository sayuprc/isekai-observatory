<?php

declare(strict_types=1);

namespace Auth\Domain\Models\Token\AccessToken;

readonly class AccessToken
{
    public function __construct(public Jwt $jwt)
    {
    }
}
