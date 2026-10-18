<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use Song\Domain\Models\SongId;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\ValueObjects\OrderNo;

/**
 * 収録曲。楽曲とタイトルの少なくとも一方を持つ
 *
 * - 楽曲 1 件: Song 集約を参照する。title があれば表示名を上書きする
 * - メドレー: 楽曲を 2 件以上、収録順に参照する。title はメドレーの表示名
 * - タイトルのみトラック: 楽曲なし / title あり(管理対象外楽曲。表示専用)
 */
readonly class Track
{
    /**
     * @param list<SongId> $songIds
     */
    private function __construct(
        public array $songIds,
        public ?TrackTitle $title,
        public OrderNo $trackNo,
    ) {
    }

    /**
     * 楽曲かタイトルの一方必須と楽曲の重複禁止は契約 (JSON Schema) で表現しない配列内ルールのため業務エラーとする
     *
     * @param list<SongId> $songIds
     *
     * @throws BusinessRuleViolationException
     */
    public static function create(array $songIds, ?TrackTitle $title, OrderNo $trackNo): self
    {
        if ($songIds === [] && $title === null) {
            throw new BusinessRuleViolationException('収録曲には楽曲かタイトルの少なくとも一方を指定してください。');
        }

        $values = array_map(static fn (SongId $songId): string => $songId->value, $songIds);

        if (count($values) !== count(array_unique($values))) {
            throw new BusinessRuleViolationException('1 つの収録曲に同じ楽曲を複数指定することはできません。');
        }

        return new self($songIds, $title, $trackNo);
    }

    /**
     * @param list<string> $songIds
     */
    public static function reconstruct(array $songIds, ?string $title, int $trackNo): self
    {
        return new self(
            array_map(static fn (string $songId): SongId => new SongId($songId), $songIds),
            $title === null ? null : new TrackTitle($title),
            new OrderNo($trackNo),
        );
    }

    /**
     * @return array{song_ids: list<string>, title: ?string, track_no: int}
     */
    public function toArray(): array
    {
        return [
            'song_ids' => array_map(static fn (SongId $songId): string => $songId->value, $this->songIds),
            'title' => $this->title?->value,
            'track_no' => $this->trackNo->value,
        ];
    }
}
