<?php

declare(strict_types=1);

namespace Event\Domain\Services;

use DateTimeImmutable;
use Event\Domain\Models\Event;
use Event\Domain\Models\EventStatus;
use Event\Domain\Models\EventType;
use Exception;
use Support\Domain\Exceptions\BusinessRuleViolationException;

/**
 * Event 集約内でしか判断できない不変条件を検証する。
 * 外部集約の存在確認は FK に委ね、ここでは関係の形だけを扱う。
 */
readonly class EventIntegrityService
{
    public function validate(Event $event): void
    {
        $this->validateSchedule($event);

        if (! in_array($event->type, [EventType::Live, EventType::Stream], true) && $event->setlist !== []) {
            throw new BusinessRuleViolationException('ライブまたは配信以外のイベントにはセットリストを設定できません');
        }

        if (
            in_array($event->status, [EventStatus::Postponed, EventStatus::Cancelled], true)
            && ($event->performances !== [] || $event->setlist !== [])
        ) {
            throw new BusinessRuleViolationException('延期または中止されたイベントには楽曲披露とセットリストを設定できません');
        }

        $this->assertUnique(array_column($event->venues, 'venue_id'), '開催先');
        $this->assertUnique(array_column($event->venues, 'order_no'), '開催先の順序');
        $this->assertUnique(array_column($event->media, 'media_id'), 'メディア');
        $this->assertUnique(array_column($event->media, 'order_no'), 'メディアの順序');
        $this->assertUnique(array_column($event->sources, 'url'), '出典URL');
        $this->assertUnique(array_column($event->sources, 'order_no'), '出典の順序');
        $this->assertUnique(array_column($event->performances, 'performance_id'), '楽曲披露');
        $this->assertUnique(array_column($event->performances, 'order_no'), '楽曲披露の順序');
        $this->assertUnique(array_column($event->setlist, 'setlist_item_id'), 'セットリスト項目');
        $this->assertUnique(array_column($event->setlist, 'order_no'), 'セットリストの順序');

        foreach ($event->performances as $performance) {
            $this->assertUnique(array_column($performance['co_vocalists'], 'person_id'), '楽曲披露の共演者');
            $this->assertUnique(array_column($performance['co_vocalists'], 'order_no'), '楽曲披露の共演者順序');
        }

        $performanceIds = array_column($event->performances, 'performance_id');
        $referencedPerformanceIds = [];
        foreach ($event->setlist as $item) {
            if ($item['label'] === null && $item['performances'] === []) {
                throw new BusinessRuleViolationException('セットリスト項目には表示名または楽曲披露を指定してください');
            }

            foreach ($item['performances'] as $performance) {
                if (! in_array($performance['performance_id'], $performanceIds, true)) {
                    throw new BusinessRuleViolationException('セットリストが存在しない楽曲披露を参照しています');
                }
                if (in_array($performance['performance_id'], $referencedPerformanceIds, true)) {
                    throw new BusinessRuleViolationException('同じ楽曲披露を複数のセットリスト項目から参照できません');
                }
                $referencedPerformanceIds[] = $performance['performance_id'];
            }
        }
    }

    private function validateSchedule(Event $event): void
    {
        if ($event->startOn === null && $event->endOn !== null) {
            throw new BusinessRuleViolationException('開催終了日だけを指定できません');
        }

        if ($event->endOn !== null && $event->startOn > $event->endOn) {
            throw new BusinessRuleViolationException('開催終了日は開始日以降を指定してください');
        }

        // 契約で形式検証済みだが、CLI や内部呼び出しでも壊れた日付を永続化しない。
        foreach ([$event->startOn, $event->endOn] as $value) {
            if ($value !== null) {
                try {
                    new DateTimeImmutable($value);
                } catch (Exception) {
                    throw new BusinessRuleViolationException('開催時期の日付形式が不正です');
                }
            }
        }
    }

    /** @param list<int|string> $values */
    private function assertUnique(array $values, string $label): void
    {
        if (count($values) !== count(array_unique($values))) {
            throw new BusinessRuleViolationException("{$label}を重複して登録できません");
        }
    }
}
