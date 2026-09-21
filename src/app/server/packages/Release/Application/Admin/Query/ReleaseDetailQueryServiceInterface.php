<?php

declare(strict_types=1);

namespace Release\Application\Admin\Query;

use Release\Domain\Models\ReleaseId;

interface ReleaseDetailQueryServiceInterface
{
    /**
     * @return list<ReleaseReferencedSong>
     */
    public function findReferencedSongs(ReleaseId $releaseId): array;
}
