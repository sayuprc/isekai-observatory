<?php

declare(strict_types=1);

namespace Auth\Domain\Services\Token\AccessToken;

interface JwtHandlerInterface
{
    public function generate(AccessTokenPayload $payload): string;

    /**
     * 検証に成功したらペイロードを返す。失効や不正な内容の場合は null
     */
    public function verify(string $jwt): ?AccessTokenPayload;
}
