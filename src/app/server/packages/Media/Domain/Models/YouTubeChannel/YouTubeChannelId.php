<?php

declare(strict_types=1);

namespace Media\Domain\Models\YouTubeChannel;

use Override;
use Support\Domain\ValueObjects\String\StringValueObject;

readonly class YouTubeChannelId extends StringValueObject
{
    #[Override]
    protected static function isValid(string $value): bool
    {
        return (bool)preg_match('/\AUC[\w-]{22}\z/', $value);
    }

    #[Override]
    protected static function getMessage(string $value): string
    {
        return "YouTube チャンネルIDの形式が不正です: {$value}";
    }
}
