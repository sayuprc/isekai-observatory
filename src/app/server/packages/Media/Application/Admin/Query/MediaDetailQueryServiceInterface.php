<?php

declare(strict_types=1);

namespace Media\Application\Admin\Query;

use Media\Domain\Models\MediaId;

interface MediaDetailQueryServiceInterface
{
    /**
     * @return array<int, MediaReferencedSong>
     */
    public function findReferencedSongs(MediaId $mediaId): array;
}
