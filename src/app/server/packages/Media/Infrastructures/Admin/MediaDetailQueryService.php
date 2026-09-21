<?php

declare(strict_types=1);

namespace Media\Infrastructures\Admin;

use Media\Application\Admin\Query\MediaDetailQueryServiceInterface;
use Media\Application\Admin\Query\MediaReferencedSong;
use Media\Domain\Models\MediaId;
use Override;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class MediaDetailQueryService implements MediaDetailQueryServiceInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function findReferencedSongs(MediaId $mediaId): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['song_media_links.song_id', 'songs.title'])
                ->select('songs.order_no', 'song_order_no')
                ->select('song_media_links.order_no', 'media_order_no')
                ->from('song_media_links')
                ->join('songs', 'song_media_links.song_id = songs.song_id')
                ->where('song_media_links.media_id', '=', $this->converter->toBin($mediaId->value))
                ->orderBy('songs.order_no')
                ->orderBy('song_media_links.order_no'),
        );

        return array_map(
            fn (array $row): MediaReferencedSong => new MediaReferencedSong(
                $this->converter->toUuid(Row::string($row, 'song_id')),
                Row::string($row, 'title'),
                Row::int($row, 'song_order_no'),
                Row::int($row, 'media_order_no'),
            ),
            $rows,
        );
    }
}
