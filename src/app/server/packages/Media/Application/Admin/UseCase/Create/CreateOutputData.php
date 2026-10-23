<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\Create;

use Media\Domain\Models\Media;
use Tag\Domain\Models\Tag;

readonly class CreateOutputData
{
    /**
     * @param array<Tag> $tags 付いているタグ。タグの表示順に並ぶ
     */
    public function __construct(
        public Media $media,
        public array $tags,
    ) {
    }
}
