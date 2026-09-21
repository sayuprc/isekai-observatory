<?php

declare(strict_types=1);

namespace App\Console\Commands\Media;

use App\Console\Commands\Concerns\ResolvesUseCaseExceptionMessage;
use Illuminate\Console\Command;
use Media\Application\Cli\UseCase\RemoveYouTubeChannel\RemoveYouTubeChannelInputData;
use Media\Application\Cli\UseCase\RemoveYouTubeChannel\RemoveYouTubeChannelUseCase;
use Override;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\UseCase\Exceptions\UseCaseException;

class RemoveYouTubeChannelCommand extends Command
{
    use ResolvesUseCaseExceptionMessage;

    #[Override]
    protected $signature = 'media:youtube-channel:remove {channelId}';

    #[Override]
    protected $description = 'インポート対象の YouTube チャンネルを削除する';

    public function handle(RemoveYouTubeChannelUseCase $useCase): int
    {
        $channelId = $this->argument('channelId');

        try {
            $useCase->handle(new RemoveYouTubeChannelInputData($channelId));
        } catch (BusinessRuleViolationException|InvalidDomainException|UseCaseException $e) {
            $this->error($this->resolveExceptionMessage($e));

            return Command::FAILURE;
        }

        $this->info(sprintf('チャンネルを削除しました: %s', $channelId));

        return Command::SUCCESS;
    }
}
