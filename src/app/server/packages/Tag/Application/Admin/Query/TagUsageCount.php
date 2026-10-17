<?php

declare(strict_types=1);

namespace Tag\Application\Admin\Query;

/**
 * タグがどれだけの記録に付いているか。管理画面の一覧に出す
 */
readonly class TagUsageCount
{
    public function __construct(public int $songCount)
    {
    }
}
