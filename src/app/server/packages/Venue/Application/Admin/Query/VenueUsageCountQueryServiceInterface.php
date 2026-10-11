<?php

declare(strict_types=1);

namespace Venue\Application\Admin\Query;

use Venue\Domain\Models\VenueId;

interface VenueUsageCountQueryServiceInterface
{
    /**
     * この開催先を使っているイベントの件数を、開催先 ID (UUID)をキーにして返す。管理画面の一覧に出す
     *
     * @param list<VenueId> $venueIds
     *
     * @return array<string, int>
     */
    public function countEvents(array $venueIds): array;
}
