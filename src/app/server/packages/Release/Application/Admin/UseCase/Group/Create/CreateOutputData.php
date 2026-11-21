<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Group\Create;

use Release\Domain\Models\ReleaseGroup;
use Tag\Domain\Models\Tag;

readonly class CreateOutputData
{
    /**
     * @param array<Tag> $tags 付いているタグ。タグの表示順に並ぶ
     */
    public function __construct(
        public ReleaseGroup $releaseGroup,
        public array $tags,
    ) {
    }
}
