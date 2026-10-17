<?php

declare(strict_types=1);

namespace Tag\Application\Admin\Query;

use Tag\Domain\Models\TagId;

interface TagUsageCountQueryServiceInterface
{
    /**
     * 渡したタグそれぞれが付いている記録の件数を、タグ ID (UUID) をキーにして返す
     *
     * @param list<TagId> $tagIds
     *
     * @return array<string, TagUsageCount>
     */
    public function countUsages(array $tagIds): array;
}
