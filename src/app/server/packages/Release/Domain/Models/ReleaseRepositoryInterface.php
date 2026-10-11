<?php

declare(strict_types=1);

namespace Release\Domain\Models;

interface ReleaseRepositoryInterface
{
    public function find(ReleaseId $releaseId): ?Release;

    /**
     * @return list<Release>
     */
    public function findByIds(ReleaseId ...$releaseIds): array;

    public function existsByReleaseGroupId(ReleaseGroupId $releaseGroupId): bool;

    public function save(Release $release): Release;

    public function delete(ReleaseId $releaseId): void;

    /**
     * イベントから関連リリースとして参照されているか
     */
    public function isUsed(ReleaseId $releaseId): bool;
}
