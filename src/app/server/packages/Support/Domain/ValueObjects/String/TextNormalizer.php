<?php

declare(strict_types=1);

namespace Support\Domain\ValueObjects\String;

use Normalizer;

final readonly class TextNormalizer
{
    /**
     * 人間が読むテキストを Unicode NFC に正規化する
     *
     * 「は」+ 結合濁点 (U+3099) のような分解形 (NFD) を、合成済みの「ば」(U+3070) に揃える
     * 外部由来 (YouTube タイトル等) の表記ゆれを取り込み口で吸収し、比較・検索を安定させる
     */
    public static function toNfc(string $value): string
    {
        $normalized = Normalizer::normalize($value, Normalizer::FORM_C);

        // 不正な UTF-8 等で正規化に失敗した場合は false が返る。値を壊さず元のまま通す
        return is_string($normalized) ? $normalized : $value;
    }
}
