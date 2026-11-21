<?php

declare(strict_types=1);

namespace AdminUser\Application\Admin\IssueRegistrationToken;

use AdminUser\Domain\Models\RegistrationToken\RegistrationToken;
use SensitiveParameter;

readonly class IssueRegistrationTokenOutputData
{
    public function __construct(
        #[SensitiveParameter]
        public RegistrationToken $token,
        #[SensitiveParameter]
        public string $plainToken,
    ) {
    }
}
