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
}
