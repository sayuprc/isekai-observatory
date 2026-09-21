<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\AddYouTubeChannel;

use Media\Domain\Models\YouTubeChannel\YouTubeChannel;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelId;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelName;
use Media\Domain\Models\YouTubeChannel\YouTubeChannelRepositoryInterface;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;

readonly class AddYouTubeChannelUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private YouTubeChannelRepositoryInterface $repository,
    ) {
    }

    public function handle(AddYouTubeChannelInputData $inputData): AddYouTubeChannelOutputData
    {
        return $this->transaction->scope(function () use ($inputData): AddYouTubeChannelOutputData {
            $channel = new YouTubeChannel(new YouTubeChannelId($inputData->channelId), new YouTubeChannelName($inputData->name));

            if (! is_null($this->repository->find($channel->channelId))) {
                throw new BusinessRuleViolationException(sprintf('すでに登録されているチャンネルです "%s"', $channel->channelId->value));
            }

            return new AddYouTubeChannelOutputData($this->repository->save($channel));
        });
    }
}
