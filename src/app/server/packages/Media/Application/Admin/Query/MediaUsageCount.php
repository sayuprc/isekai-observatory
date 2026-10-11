<?php

declare(strict_types=1);

namespace Media\Application\Admin\Query;

/**
 * メディアがどれだけ参照されているか。管理画面の一覧に出す
 */
readonly class MediaUsageCount
{
    public function __construct(
        public int $songCount,
        public int $eventCount,
    ) {
    }
}
