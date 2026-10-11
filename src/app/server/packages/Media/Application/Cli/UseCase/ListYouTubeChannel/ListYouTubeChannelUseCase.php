<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\ListYouTubeChannel;

use Media\Domain\Models\YouTubeChannel\YouTubeChannelRepositoryInterface;

readonly class ListYouTubeChannelUseCase
{
    public function __construct(private YouTubeChannelRepositoryInterface $repository)
    {
    }

    public function handle(ListYouTubeChannelInputData $inputData): ListYouTubeChannelOutputData
    {
        return new ListYouTubeChannelOutputData($this->repository->findAll());
    }
}
