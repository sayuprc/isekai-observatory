<?php

declare(strict_types=1);

namespace AdminUser\Application\Cli\UseCase\IssueRegistrationToken;

use AdminUser\Domain\Models\RegistrationToken\RegistrationToken;

readonly class IssueRegistrationTokenOutputData
{
    public function __construct(
        public RegistrationToken $token,
        public string $plainToken,
    ) {
    }
}
