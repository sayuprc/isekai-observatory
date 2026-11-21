<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Recovery;

use Auth\Domain\Models\Token\AccessToken\AccessToken;
use SensitiveParameter;

readonly class RecoveryFinishOutputData
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
