<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\RegisterFinish;

use Auth\Domain\Models\Token\AccessToken\AccessToken;
use SensitiveParameter;

readonly class RegisterFinishOutputData
{
    public function __construct(
        #[SensitiveParameter]
        public AccessToken $accessToken,
        public string $refreshTokenId,
        #[SensitiveParameter]
        public string $plainRefreshToken,
    ) {
    }
}
