<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use Override;
use Support\Domain\ValueObjects\String\TextValueObject;

/**
 * 媒体の Disc 表示用ラベル(CD1 / Blu-ray など)。任意項目で、無しは null で表現する
 */
readonly class MediumName extends TextValueObject
{
    #[Override]
    protected static function isValid(string $value): bool
    {
        return $value !== '';
    }

    #[Override]
    protected static function getMessage(string $value): string
    {
        return '媒体の表示ラベルを空にすることはできません';
    }
}
