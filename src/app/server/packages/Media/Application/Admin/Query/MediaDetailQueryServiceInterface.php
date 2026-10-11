<?php

declare(strict_types=1);

namespace Media\Application\Admin\Query;

use Media\Domain\Models\MediaId;

interface MediaDetailQueryServiceInterface
{
    /**
     * @return list<MediaReferencedSong>
     */
    public function findReferencedSongs(MediaId $mediaId): array;
}
