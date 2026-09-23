<?php

declare(strict_types=1);

namespace Event\Domain\Models\Performances;

use Song\Domain\Models\SongId;
use Support\Collection\ImmutableCollection;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\ValueObjects\OrderNo;

/**
 * @extends ImmutableCollection<int, SongPerformance>
 */
readonly class SongPerformances extends ImmutableCollection
{
    /**
     * @param list<array{performanceId: string, songId: string, orderNo: int, coVocalists: list<array{personId: string, creditName: ?string, orderNo: int}>}> $items
     *
     * @throws BusinessRuleViolationException
     */
    public static function fromArray(array $items): self
    {
        if (count($items) !== count(array_unique(array_column($items, 'performanceId')))) {
            throw new BusinessRuleViolationException('楽曲披露を重複して登録できません');
        }

        if (count($items) !== count(array_unique(array_column($items, 'orderNo')))) {
            throw new BusinessRuleViolationException('楽曲披露の順序を重複して登録できません');
        }

        return new self(array_map(
            static fn (array $item): SongPerformance => new SongPerformance(
                new PerformanceId($item['performanceId']),
                new SongId($item['songId']),
                new OrderNo($item['orderNo']),
                CoVocalists::fromArray($item['coVocalists']),
            ),
            $items,
        ));
    }

    /**
     * @param list<array{performanceId: string, songId: string, orderNo: int, coVocalists: list<array{personId: string, creditName: ?string, orderNo: int}>}> $items
     */
    public static function reconstruct(array $items): self
    {
        return new self(array_map(
            static fn (array $item): SongPerformance => new SongPerformance(
                new PerformanceId($item['performanceId']),
                new SongId($item['songId']),
                new OrderNo($item['orderNo']),
                CoVocalists::reconstruct($item['coVocalists']),
            ),
            $items,
        ));
    }

    public function contains(PerformanceId $performanceId): bool
    {
        foreach ($this->items as $performance) {
            if ($performance->performanceId->equals($performanceId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<array{performance_id: string, song_id: string, order_no: int, co_vocalists: list<array{person_id: string, credit_name: ?string, order_no: int}>}>
     */
    public function toArray(): array
    {
        return array_values(array_map(static fn (SongPerformance $performance): array => $performance->toArray(), $this->items));
    }
}
