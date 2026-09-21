<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use Song\Domain\Models\SongId;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\ValueObjects\OrderNo;

/**
 * 収録曲。songId と title の少なくとも一方を持つ
 *
 * - 参照トラック: songId あり(Song 集約を参照する)。title があれば表示名を上書きする
 * - タイトルのみトラック: songId なし / title あり(管理対象外楽曲。表示専用)
 */
readonly class Track
{
    private function __construct(
        public ?SongId $songId,
        public ?TrackTitle $title,
        public OrderNo $trackNo,
    ) {
    }

    /**
     * 楽曲かタイトルの一方必須は契約 (JSON Schema) で表現しない配列内ルールのため業務エラーとする
     *
     * @throws BusinessRuleViolationException
     */
    public static function create(?SongId $songId, ?TrackTitle $title, OrderNo $trackNo): self
    {
        if (is_null($songId) && is_null($title)) {
            throw new BusinessRuleViolationException('収録曲には楽曲かタイトルの少なくとも一方を指定してください。');
        }

        return new self($songId, $title, $trackNo);
    }

    public static function reconstruct(?string $songId, ?string $title, int $trackNo): self
    {
        return new self(
            is_null($songId) ? null : new SongId($songId),
            is_null($title) ? null : new TrackTitle($title),
            new OrderNo($trackNo),
        );
    }

    /**
     * @return array{song_id: ?string, title: ?string, track_no: int}
     */
    public function toArray(): array
    {
        return [
            'song_id' => $this->songId?->value,
            'title' => $this->title?->value,
            'track_no' => $this->trackNo->value,
        ];
    }
}
