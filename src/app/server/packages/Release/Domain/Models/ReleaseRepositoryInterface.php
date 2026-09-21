<?php

declare(strict_types=1);

namespace Release\Domain\Models;

interface ReleaseRepositoryInterface
{
    public function find(ReleaseId $releaseId): ?Release;

    public function existsByReleaseGroupId(ReleaseGroupId $releaseGroupId): bool;

    public function save(Release $release): Release;

    public function delete(ReleaseId $releaseId): void;
}
