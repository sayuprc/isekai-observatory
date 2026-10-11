<?php

declare(strict_types=1);

namespace Person\Application\Admin\Query;

interface PersonGroupUsageCountQueryServiceInterface
{
    /**
     * このグループとして共演が記録されている楽曲披露の件数を、人物グループ ID (UUID)をキーにして返す。管理画面の一覧に出す
     *
     * @param list<string> $personGroupIds
     *
     * @return array<string, int>
     */
    public function countPerformances(array $personGroupIds): array;
}
