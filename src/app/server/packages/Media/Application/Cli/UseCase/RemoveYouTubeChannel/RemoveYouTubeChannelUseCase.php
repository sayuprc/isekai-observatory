<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\RemoveYouTubeChannel;

use Media\Domain\Models\YouTubeChannel\YouTubeChannelId;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelRepositoryInterface;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;

readonly class RemoveYouTubeChannelUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private YouTubeChannelRepositoryInterface $repository,
    ) {
    }

    public function handle(RemoveYouTubeChannelInputData $inputData): void
    {
        $this->transaction->scope(function () use ($inputData): void {
            $channelId = new YouTubeChannelId($inputData->channelId);

            if (is_null($this->repository->find($channelId))) {
                throw new BusinessRuleViolationException(sprintf('登録されていないチャンネルです "%s"', $channelId->value));
            }

            $this->repository->delete($channelId);
        });
    }
}
