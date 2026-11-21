<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Get\Query;

use Release\Domain\Models\ReleaseId;

interface ReleaseDetailQueryServiceInterface
{
    /**
     * @return array<int, ReleaseReferencedSong>
     */
    public function findReferencedSongs(ReleaseId $releaseId): array;
}
