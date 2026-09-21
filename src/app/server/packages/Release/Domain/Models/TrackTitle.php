<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use Override;
use Support\Domain\ValueObjects\String\TextValueObject;

/**
 * トラックの表示名。参照トラックでは楽曲名の上書き、管理対象外楽曲ではそのタイトル
 * Song 集約の title とは別物として Release 側に置く
 */
readonly class TrackTitle extends TextValueObject
{
    #[Override]
    protected static function isValid(string $value): bool
    {
        return $value !== '';
    }

    #[Override]
    protected static function getMessage(string $value): string
    {
        return 'トラックタイトルは必須です';
    }
}
