<?php

declare(strict_types=1);

namespace Media\Application\Admin\Query;

use Media\Domain\Models\MediaId;

interface MediaUsageCountQueryServiceInterface
{
    /**
     * 渡したメディアそれぞれの参照件数を、メディア ID (UUID) をキーにして返す
     *
     * @param list<MediaId> $mediaIds
     *
     * @return array<string, MediaUsageCount>
     */
    public function countUsages(array $mediaIds): array;
}
