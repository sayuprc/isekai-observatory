<?php

declare(strict_types=1);

namespace Person\Application\Admin\Query;

use Person\Domain\Models\PersonId;

interface PersonUsageCountQueryServiceInterface
{
    /**
     * 渡した人物それぞれの参照件数を、人物 ID (UUID) をキーにして返す
     *
     * @param list<PersonId> $personIds
     *
     * @return array<string, PersonUsageCount>
     */
    public function countUsages(array $personIds): array;
}
