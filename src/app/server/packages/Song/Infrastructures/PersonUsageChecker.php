<?php

declare(strict_types=1);

namespace Song\Infrastructures;

use Override;
use Person\Domain\Models\PersonId;
use Person\Domain\Services\PersonUsageCheckerInterface;
use Song\Domain\Models\SongRepositoryInterface;

readonly class PersonUsageChecker implements PersonUsageCheckerInterface
{
    public function __construct(private SongRepositoryInterface $repository)
    {
    }

    #[Override]
    public function isUsed(PersonId $personId): bool
    {
        return $this->repository->isPersonUsed($personId);
    }
}
