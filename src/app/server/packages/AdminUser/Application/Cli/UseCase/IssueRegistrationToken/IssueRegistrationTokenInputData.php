<?php

declare(strict_types=1);

namespace AdminUser\Application\Cli\UseCase\IssueRegistrationToken;

readonly class IssueRegistrationTokenInputData
{
    /**
     * @param list<string> $permissions
     */
    public function __construct(
        public string $email,
        public int $role,
        public array $permissions,
    ) {
    }
}
