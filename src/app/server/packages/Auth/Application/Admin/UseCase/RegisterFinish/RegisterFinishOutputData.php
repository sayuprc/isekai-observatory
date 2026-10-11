<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\RegisterFinish;

use Auth\Domain\Models\Token\AccessToken\AccessToken;

readonly class RegisterFinishOutputData
{
    public function __construct(
        public AccessToken $accessToken,
        public string $refreshTokenId,
        public string $plainRefreshToken,
    ) {
    }
}
