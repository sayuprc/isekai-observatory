<?php

declare(strict_types=1);

namespace Auth\Application\Admin\Refresh;

use SensitiveParameter;

readonly class RefreshInputData
{
    public function __construct(
        public string $refreshTokenId,
        #[SensitiveParameter]
        public string $refreshToken,
    ) {
    }
}
