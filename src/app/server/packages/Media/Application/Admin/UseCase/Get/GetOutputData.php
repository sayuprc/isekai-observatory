<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\Get;

use Media\Application\Admin\Query\MediaReferencedSong;
use Media\Domain\Models\Media;
use Tag\Domain\Models\Tag;

readonly class GetOutputData
{
    /**
     * @param array<int, MediaReferencedSong> $songs
     * @param array<Tag>                      $tags  付いているタグ。タグの表示順に並ぶ
     */
    public function __construct(
        public Media $media,
        public array $songs,
        public array $tags,
    ) {
    }
}
