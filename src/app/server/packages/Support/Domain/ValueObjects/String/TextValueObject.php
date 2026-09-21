<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\String;

/**
 * 人間が読むテキストを表す値オブジェクトの基底
 *
 * 生成・再構築の入口で値を Unicode NFC に正規化し、「title は常に NFC」という不変条件を保証する
 * JWT やハッシュ値のようにバイト完全一致が要る値は {@see StringValueObject} を直接継承すること
 */
abstract readonly class TextValueObject extends StringValueObject
{
    public function __construct(string $value)
    {
        parent::__construct(TextNormalizer::toNfc($value));
    }
}
