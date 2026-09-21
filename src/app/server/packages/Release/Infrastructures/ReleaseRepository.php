<?php

declare(strict_types=1);

namespace Release\Infrastructures;

use DateTimeImmutable;
use DateType\ImmutableDate;
use Override;
use Release\Domain\Models\Release;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseId;
use Release\Domain\Models\ReleaseRepositoryInterface;
use Support\Contracts\Uuid\UuidConverterInterface;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class ReleaseRepository implements ReleaseRepositoryInterface
{
    private const string TABLE = 'releases';

    private const string FORMAT_TABLE = 'release_formats';

    private const string MEDIA_TABLE = 'release_media';

    private const string TRACK_TABLE = 'release_tracks';

    /** @var list<string> */
    private const array COLUMNS = ['release_id', 'release_group_id', 'name', 'released_on', 'description', 'color', 'is_display', 'order_no'];

    public function __construct(
        private QueryFactory $queryFactory,
        private UuidConverterInterface $converter,
    ) {
    }

    #[Override]
    public function find(ReleaseId $releaseId): ?Release
    {
        $binReleaseId = $this->converter->toBin($releaseId->value);

        $releaseRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('release_id', '=', $binReleaseId)
                ->limit(1),
        );

        $releaseRow = $releaseRows[0] ?? null;

        if (is_null($releaseRow)) {
            return null;
        }

        return $this->hydrate($releaseRow, $this->loadFormats($binReleaseId), $this->loadMedia($binReleaseId));
    }

    #[Override]
    public function existsByReleaseGroupId(ReleaseGroupId $releaseGroupId): bool
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['release_id'])
                ->from(self::TABLE)
                ->where('release_group_id', '=', $this->converter->toBin($releaseGroupId->value))
                ->limit(1),
        );

        return $rows !== [];
    }

    #[Override]
    public function save(Release $release): Release
    {
        $binReleaseId = $this->converter->toBin($release->releaseId->value);
        $data = $release->toArray();
        $now = now()->toDateTimeString();

        // 提供形態・媒体・収録曲は洗い替えする(収録曲は FK CASCADE で媒体と一緒に消える)
        $this->queryFactory->delete()
            ->from(self::FORMAT_TABLE)
            ->where('release_id', '=', $binReleaseId)
            ->execute($this->queryFactory->pdo());

        $this->queryFactory->delete()
            ->from(self::MEDIA_TABLE)
            ->where('release_id', '=', $binReleaseId)
            ->execute($this->queryFactory->pdo());

        $this->queryFactory->insert()
            ->into(self::TABLE, ['release_id', 'release_group_id', 'name', 'released_on', 'description', 'color', 'is_display', 'order_no', 'created_at', 'updated_at'])
            ->values([
                $binReleaseId,
                $this->converter->toBin($data['release_group_id']),
                $data['name'],
                $data['released_on'],
                $data['description'],
                $data['color'],
                $data['is_display'],
                $data['order_no'],
                $now,
                $now,
            ])
            ->build()
            ->append(
                'ON DUPLICATE KEY UPDATE '
                . '`release_group_id` = VALUES(`release_group_id`), '
                . '`name` = VALUES(`name`), '
                . '`released_on` = VALUES(`released_on`), '
                . '`description` = VALUES(`description`), '
                . '`color` = VALUES(`color`), '
                . '`is_display` = VALUES(`is_display`), '
                . '`order_no` = VALUES(`order_no`), '
                . '`updated_at` = VALUES(`updated_at`)',
            )
            ->execute($this->queryFactory->pdo());

        $formatRows = array_map(
            static fn (int $format): array => [$binReleaseId, $format],
            $data['formats'],
        );

        if ($formatRows !== []) {
            $this->queryFactory->insert()
                ->into(self::FORMAT_TABLE, ['release_id', 'format'])
                ->values(...$formatRows)
                ->execute($this->queryFactory->pdo());
        }

        $mediumRows = [];
        $trackRows = [];

        foreach ($data['media'] as $medium) {
            $mediumRows[] = [$binReleaseId, $medium['position'], $medium['name']];

            foreach ($medium['tracks'] as $track) {
                $trackRows[] = [
                    $binReleaseId,
                    $medium['position'],
                    $track['track_no'],
                    is_null($track['song_id']) ? null : $this->converter->toBin($track['song_id']),
                    $track['title'],
                ];
            }
        }

        if ($mediumRows !== []) {
            $this->queryFactory->insert()
                ->into(self::MEDIA_TABLE, ['release_id', 'position', 'name'])
                ->values(...$mediumRows)
                ->execute($this->queryFactory->pdo());
        }

        if ($trackRows !== []) {
            $this->queryFactory->insert()
                ->into(self::TRACK_TABLE, ['release_id', 'position', 'track_no', 'song_id', 'title'])
                ->values(...$trackRows)
                ->execute($this->queryFactory->pdo());
        }

        return $release;
    }

    #[Override]
    public function delete(ReleaseId $releaseId): void
    {
        $this->queryFactory->delete()
            ->from(self::TABLE)
            ->where('release_id', '=', $this->converter->toBin($releaseId->value))
            ->execute($this->queryFactory->pdo());
    }

    /**
     * @return list<int>
     */
    private function loadFormats(string $binReleaseId): array
    {
        $formatRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['format'])
                ->from(self::FORMAT_TABLE)
                ->where('release_id', '=', $binReleaseId)
                ->orderBy('format'),
        );

        return array_map(
            static fn (array $row): int => Row::int($row, 'format'),
            $formatRows,
        );
    }

    /**
     * @return list<array{position: int, name: ?string, tracks: list<array{songId: ?string, title: ?string, trackNo: int}>}>
     */
    private function loadMedia(string $binReleaseId): array
    {
        $mediumRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['position', 'name'])
                ->from(self::MEDIA_TABLE)
                ->where('release_id', '=', $binReleaseId)
                ->orderBy('position'),
        );

        $trackRows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(['position', 'track_no', 'song_id', 'title'])
                ->from(self::TRACK_TABLE)
                ->where('release_id', '=', $binReleaseId)
                ->orderBy('position')
                ->orderBy('track_no'),
        );

        $tracksByPosition = [];

        foreach ($trackRows as $trackRow) {
            $binSongId = Row::nullableString($trackRow, 'song_id');

            $tracksByPosition[Row::int($trackRow, 'position')][] = [
                'songId' => is_null($binSongId) ? null : $this->converter->toUuid($binSongId),
                'title' => Row::nullableString($trackRow, 'title'),
                'trackNo' => Row::int($trackRow, 'track_no'),
            ];
        }

        return array_map(
            static fn (array $mediumRow): array => [
                'position' => Row::int($mediumRow, 'position'),
                'name' => Row::nullableString($mediumRow, 'name'),
                'tracks' => $tracksByPosition[Row::int($mediumRow, 'position')] ?? [],
            ],
            $mediumRows,
        );
    }

    /**
     * @param array<string, mixed>                                                                                          $releaseRow
     * @param list<int>                                                                                                     $formats
     * @param list<array{position: int, name: ?string, tracks: list<array{songId: ?string, title: ?string, trackNo: int}>}> $media
     */
    private function hydrate(array $releaseRow, array $formats, array $media): Release
    {
        return Release::reconstruct(
            $this->converter->toUuid(Row::string($releaseRow, 'release_id')),
            $this->converter->toUuid(Row::string($releaseRow, 'release_group_id')),
            Row::string($releaseRow, 'name'),
            ImmutableDate::createFromInterface(new DateTimeImmutable(Row::string($releaseRow, 'released_on'))),
            Row::string($releaseRow, 'description'),
            Row::string($releaseRow, 'color'),
            Row::bool($releaseRow, 'is_display'),
            Row::int($releaseRow, 'order_no'),
            $formats,
            $media,
        );
    }
}
