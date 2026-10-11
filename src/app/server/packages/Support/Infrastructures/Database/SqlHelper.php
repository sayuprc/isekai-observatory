<?php

declare(strict_types=1);

namespace Support\Infrastructures\Database;

class SqlHelper
{
    /**
     * @pure
     */
    public static function escapeLike(string $keyword): string
    {
        return addcslashes($keyword, '%_\\');
    }

    /**
     * 部分一致検索 (LIKE '%keyword%') のパターンを作る
     *
     * @pure
     */
    public static function containsPattern(string $keyword): string
    {
        return '%' . self::escapeLike($keyword) . '%';
    }
}
