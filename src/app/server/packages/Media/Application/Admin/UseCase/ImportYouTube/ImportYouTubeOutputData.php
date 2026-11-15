<?php

declare(strict_types=1);

namespace Media\Application\Admin\UseCase\ImportYouTube;

readonly class ImportYouTubeOutputData
{
    /**
     * @param array<int, ChannelImportResult> $results
     */
    public function __construct(public array $results)
    {
    }
}
