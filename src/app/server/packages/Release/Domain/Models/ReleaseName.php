<?php

declare(strict_types=1);

namespace Release\Domain\Models;

use Support\Domain\ValueObjects\String\TextValueObject;

/**
 * リリースの版名(通常盤 / 初回限定盤 など)
 * 任意項目で、無しは空文字で表現する
 */
readonly class ReleaseName extends TextValueObject
{
}
