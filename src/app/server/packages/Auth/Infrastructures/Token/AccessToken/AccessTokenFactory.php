<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Token\AccessToken;

use Auth\Domain\Models\Token\AccessToken\AccessToken;
use Auth\Domain\Models\Token\AccessToken\AccessTokenFactoryInterface;
use Auth\Domain\Models\Token\AccessToken\Jwt;
use Auth\Domain\Services\Token\AccessToken\AccessTokenPayload;
use Auth\Domain\Services\Token\AccessToken\JwtHandlerInterface;
use Override;

readonly class AccessTokenFactory implements AccessTokenFactoryInterface
{
    public function __construct(private JwtHandlerInterface $jwt)
    {
    }

    #[Override]
    public function create(AccessTokenPayload $payload): AccessToken
    {
        return new AccessToken(new Jwt($this->jwt->generate($payload)));
    }
}
