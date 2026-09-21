<?php

declare(strict_types=1);

namespace Song\Infrastructures;

use Override;
use Person\Domain\Models\PersonId;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class SongRepository implements SongRepositoryInterface
{
    /** @var list<string> */
    private const array SONG_COLUMNS = [
        'song_id',
        'title',
        'description',
        'lyrics_link',
        'type',
        'is_display',
        'order_no',
    ];

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
        private SongTagRepositoryInterface $songTagRepository,
    ) {
    }

    #[Override]
    public function find(SongId $songId): ?Song
    {
        $binSongId = $this->converter->toBin($songId->value);

        $songRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::SONG_COLUMNS)
                ->from('songs')
                ->where('song_id', '=', $binSongId)
                ->limit(1),
        );

        $songRow = $songRows[0] ?? null;

        if (is_null($songRow)) {
            return null;
        }

        $personRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['person_id', 'role', 'order_no'])
                ->from('song_persons')
                ->where('song_id', '=', $binSongId)
                ->orderBy('order_no'),
        );

        $taggingRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['song_tag_id'])
                ->from('song_taggings')
                ->where('song_id', '=', $binSongId),
        );

        $mediaRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['media_id', 'order_no'])
                ->from('song_media_links')
                ->where('song_id', '=', $binSongId)
                ->orderBy('order_no'),
        );

        return $this->hydrate($songRow, $personRows, $taggingRows, $mediaRows);
    }

    #[Override]
    public function isPersonUsed(PersonId $personId): bool
    {
        $count = Row::intValue(
            $this->queryFactory->select()
                ->from('song_persons')
                ->where('person_id', '=', $this->converter->toBin($personId->value))
                ->aggregate($this->queryFactory->pdo(), 'COUNT(*)'),
        );

        return $count > 0;
    }

    #[Override]
    public function save(Song $song): Song
    {
        $id = $this->converter->toBin($song->songId->value);
        $data = $song->toArray();

        // 子テーブルは洗い替えする
        $this->deleteChildren($id);

        $now = now()->toDateTimeString();

        $this->queryFactory->insert()
            ->into('songs', ['song_id', 'title', 'description', 'lyrics_link', 'type', 'is_display', 'order_no', 'created_at', 'updated_at'])
            ->values([
                $id,
                $data['title'],
                $data['description'],
                $data['lyrics_link'],
                $data['type'],
                $data['is_display'],
                $data['order_no'],
                $now,
                $now,
            ])
            ->build()
            ->append(
                'ON DUPLICATE KEY UPDATE '
                . '`title` = VALUES(`title`), '
                . '`description` = VALUES(`description`), '
                . '`lyrics_link` = VALUES(`lyrics_link`), '
                . '`type` = VALUES(`type`), '
                . '`is_display` = VALUES(`is_display`), '
                . '`order_no` = VALUES(`order_no`), '
                . '`updated_at` = VALUES(`updated_at`)',
            )
            ->execute($this->queryFactory->pdo());

        $this->insertPersons($id, $data['persons']);
        $this->insertTaggings($id, $data['tags']);
        $this->insertMedia($id, $data['media']);

        return $this->find($song->songId) ?? $song;
    }

    #[Override]
    public function delete(SongId $songId): void
    {
        $this->queryFactory->delete()
            ->from('songs')
            ->where('song_id', '=', $this->converter->toBin($songId->value))
            ->execute($this->queryFactory->pdo());
    }

    #[Override]
    public function getMaxOrderNo(): int
    {
        $max = $this->queryFactory->select()
            ->from('songs')
            ->aggregate($this->queryFactory->pdo(), 'MAX(order_no)');

        return Row::intValue($max);
    }

    private function deleteChildren(string $binSongId): void
    {
        foreach (['song_persons', 'song_taggings', 'song_media_links'] as $table) {
            $this->queryFactory->delete()
                ->from($table)
                ->where('song_id', '=', $binSongId)
                ->execute($this->queryFactory->pdo());
        }
    }

    /**
     * @param array<int, array{person_id: string, role: int, order_no: int}> $persons
     */
    private function insertPersons(string $binSongId, array $persons): void
    {
        if ($persons === []) {
            return;
        }

        $rows = array_map(
            fn (array $person): array => [
                $binSongId,
                $this->converter->toBin($person['person_id']),
                $person['role'],
                $person['order_no'],
            ],
            $persons,
        );

        $this->queryFactory->insert()
            ->into('song_persons', ['song_id', 'person_id', 'role', 'order_no'])
            ->values(...$rows)
            ->execute($this->queryFactory->pdo());
    }

    /**
     * @param array<int, array{song_tag_id: string}> $tags
     */
    private function insertTaggings(string $binSongId, array $tags): void
    {
        if ($tags === []) {
            return;
        }

        $rows = array_map(
            fn (array $tag): array => [
                $binSongId,
                $this->converter->toBin($tag['song_tag_id']),
            ],
            $tags,
        );

        $this->queryFactory->insert()
            ->into('song_taggings', ['song_id', 'song_tag_id'])
            ->values(...$rows)
            ->execute($this->queryFactory->pdo());
    }

    /**
     * @param array<int, array{media_id: string, order_no: int}> $media
     */
    private function insertMedia(string $binSongId, array $media): void
    {
        if ($media === []) {
            return;
        }

        $rows = array_map(
            fn (array $item): array => [
                $binSongId,
                $this->converter->toBin($item['media_id']),
                $item['order_no'],
            ],
            $media,
        );

        $this->queryFactory->insert()
            ->into('song_media_links', ['song_id', 'media_id', 'order_no'])
            ->values(...$rows)
            ->execute($this->queryFactory->pdo());
    }

    /**
     * @param array<string, mixed>       $songRow
     * @param list<array<string, mixed>> $personRows
     * @param list<array<string, mixed>> $taggingRows
     * @param list<array<string, mixed>> $mediaRows
     */
    private function hydrate(array $songRow, array $personRows, array $taggingRows, array $mediaRows): Song
    {
        $persons = array_map(
            fn (array $person): array => [
                'personId' => $this->converter->toUuid(Row::string($person, 'person_id')),
                'role' => Row::int($person, 'role'),
                'orderNo' => Row::int($person, 'order_no'),
            ],
            $personRows,
        );

        $tags = $this->sortTagsByMasterOrder(
            array_map(
                fn (array $tag): array => ['songTagId' => $this->converter->toUuid(Row::string($tag, 'song_tag_id'))],
                $taggingRows,
            ),
        );

        $media = array_map(
            fn (array $item): array => [
                'mediaId' => $this->converter->toUuid(Row::string($item, 'media_id')),
                'orderNo' => Row::int($item, 'order_no'),
            ],
            $mediaRows,
        );

        return Song::reconstruct(
            $this->converter->toUuid(Row::string($songRow, 'song_id')),
            Row::string($songRow, 'title'),
            Row::string($songRow, 'description'),
            Row::nullableString($songRow, 'lyrics_link'),
            Row::int($songRow, 'type'),
            Row::bool($songRow, 'is_display'),
            Row::int($songRow, 'order_no'),
            $tags,
            $persons,
            $media,
        );
    }

    /**
     * @param list<array{songTagId: string}> $tags
     *
     * @return list<array{songTagId: string}>
     */
    private function sortTagsByMasterOrder(array $tags): array
    {
        if ($tags === []) {
            return [];
        }

        $foundTags = $this->songTagRepository->findByIds(
            ...array_map(
                static fn (array $tag): SongTagId => new SongTagId($tag['songTagId']),
                $tags,
            ),
        );

        return array_map(
            static fn (SongTag $tag): array => ['songTagId' => $tag->songTagId->value],
            $foundTags,
        ) |> array_values(...);
    }
}
