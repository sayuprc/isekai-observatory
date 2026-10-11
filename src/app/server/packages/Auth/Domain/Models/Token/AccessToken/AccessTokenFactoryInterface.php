<?php

declare(strict_types=1);

namespace Auth\Domain\Models\Token\AccessToken;

use Auth\Domain\Services\Token\AccessToken\AccessTokenPayload;

interface AccessTokenFactoryInterface
{
    public function create(AccessTokenPayload $payload): AccessToken;
}
