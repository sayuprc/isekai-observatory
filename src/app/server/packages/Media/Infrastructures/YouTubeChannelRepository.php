<?php

declare(strict_types=1);

namespace Media\Infrastructures;

use Media\Domain\Models\YouTubeChannel\YouTubeChannel;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelId;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelRepositoryInterface;
use Override;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;

readonly class YouTubeChannelRepository implements YouTubeChannelRepositoryInterface
{
    private const string TABLE = 'youtube_channels';

    /** @var list<string> */
    private const array COLUMNS = [
        'channel_id',
        'name',
    ];

    public function __construct(private QueryFactory $queryFactory)
    {
    }

    #[Override]
    public function find(YouTubeChannelId $channelId): ?YouTubeChannel
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->where('channel_id', '=', $channelId->value)
                ->limit(1),
        );

        $row = $rows[0] ?? null;

        return is_null($row) ? null : $this->hydrate($row);
    }

    #[Override]
    public function findAll(): array
    {
        $rows = $this->queryFactory->fetchAll(
            $this->queryFactory->select()
                ->withSelect(self::COLUMNS)
                ->from(self::TABLE)
                ->orderBy('created_at')
                ->orderBy('channel_id'),
        );

        return array_map($this->hydrate(...), $rows);
    }

    #[Override]
    public function save(YouTubeChannel $channel): YouTubeChannel
    {
        $data = $channel->toArray();
        $now = now()->toDateTimeString();

        $this->queryFactory->insert()
            ->into(self::TABLE, [
                'channel_id',
                'name',
                'created_at',
                'updated_at',
            ])
            ->values([
                $data['channel_id'],
                $data['name'],
                $now,
                $now,
            ])
            ->build()
            ->append(
                'ON DUPLICATE KEY UPDATE '
                    . '`name` = VALUES(`name`), '
                    . '`updated_at` = VALUES(`updated_at`)',
            )
            ->execute($this->queryFactory->pdo());

        return $channel;
    }

    #[Override]
    public function delete(YouTubeChannelId $channelId): void
    {
        $this->queryFactory->delete()
            ->from(self::TABLE)
            ->where('channel_id', '=', $channelId->value)
            ->execute($this->queryFactory->pdo());
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): YouTubeChannel
    {
        return YouTubeChannel::reconstruct(
            Row::string($row, 'channel_id'),
            Row::string($row, 'name'),
        );
    }
}
