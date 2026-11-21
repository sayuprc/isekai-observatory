<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Get\Query;

use Release\Domain\Models\ReleaseGroupId;

interface ReleaseGroupDetailQueryServiceInterface
{
    /**
     * @return array<int, ReleaseGroupReferencedRelease>
     */
    public function findReferencedReleases(ReleaseGroupId $releaseGroupId): array;
}
