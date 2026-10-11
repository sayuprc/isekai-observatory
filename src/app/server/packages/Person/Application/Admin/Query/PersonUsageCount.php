<?php

declare(strict_types=1);

namespace Person\Application\Admin\Query;

/**
 * 人物がどれだけ参照されているか。管理画面の一覧に出す
 */
readonly class PersonUsageCount
{
    public function __construct(
        public int $songCount,
        public int $performanceCount,
    ) {
    }
}
