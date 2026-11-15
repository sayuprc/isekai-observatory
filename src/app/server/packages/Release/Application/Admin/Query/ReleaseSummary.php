<?php

declare(strict_types=1);

namespace Release\Application\Admin\Query;

readonly class ReleaseSummary
{
    /**
     * @param bool            $isDisplay    リリースとリリースグループがともに公開のとき true
     * @param array<int, int> $formatValues
     */
    public function __construct(
        public string $releaseId,
        public string $releaseGroupId,
        public string $releaseGroupTitle,
        public string $name,
        public string $releasedOn,
        public bool $isDisplay,
        public array $formatValues,
    ) {
    }
}
