<?php

declare(strict_types=1);

namespace Event\Domain\Models;

use DateMalformedStringException;
use DateType\ImmutableDate;
use Support\Domain\Exceptions\BusinessRuleViolationException;

/**
 * 開催時期。両方 null は日付未定、startOn のみは単日、両方指定は期間を表す
 */
readonly class EventSchedule
{
    public function __construct(
        public ?ImmutableDate $startOn,
        public ?ImmutableDate $endOn,
    ) {
    }

    /**
     * @throws BusinessRuleViolationException
     */
    public static function fromArray(?string $startOn, ?string $endOn): self
    {
        $schedule = new self(self::toDate($startOn), self::toDate($endOn));

        if ($schedule->startOn === null && $schedule->endOn !== null) {
            throw new BusinessRuleViolationException('開催終了日だけを指定できません');
        }

        if ($schedule->startOn !== null && $schedule->endOn !== null && $schedule->startOn > $schedule->endOn) {
            throw new BusinessRuleViolationException('開催終了日は開始日以降を指定してください');
        }

        return $schedule;
    }

    public static function reconstruct(?string $startOn, ?string $endOn): self
    {
        return new self(
            $startOn === null ? null : new ImmutableDate($startOn),
            $endOn === null ? null : new ImmutableDate($endOn),
        );
    }

    /**
     * @return array{start_on: ?string, end_on: ?string}
     */
    public function toArray(): array
    {
        return [
            'start_on' => $this->startOn?->format('Y-m-d'),
            'end_on' => $this->endOn?->format('Y-m-d'),
        ];
    }

    /**
     * @throws BusinessRuleViolationException
     */
    private static function toDate(?string $value): ?ImmutableDate
    {
        if ($value === null) {
            return null;
        }

        try {
            return new ImmutableDate($value);
        } catch (DateMalformedStringException) {
            throw new BusinessRuleViolationException('開催時期の日付形式が不正です');
        }
    }
}
