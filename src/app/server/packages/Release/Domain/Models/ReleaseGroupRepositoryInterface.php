<?php

declare(strict_types=1);

namespace Release\Domain\Models;

interface ReleaseGroupRepositoryInterface
{
    public function find(ReleaseGroupId $releaseGroupId): ?ReleaseGroup;

    /**
     * @return list<ReleaseGroup>
     */
    public function findByIds(ReleaseGroupId ...$releaseGroupIds): array;

    public function save(ReleaseGroup $releaseGroup): ReleaseGroup;

    public function delete(ReleaseGroupId $releaseGroupId): void;
}
