<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\ImportYouTube;

readonly class ImportYouTubeOutputData
{
    /**
     * @param array<int, ChannelImportResult> $results
     */
    public function __construct(public array $results)
    {
    }
}
