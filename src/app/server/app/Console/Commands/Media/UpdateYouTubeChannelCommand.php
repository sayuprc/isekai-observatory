<?php

declare(strict_types=1);

namespace App\Console\Commands\Media;

use App\Console\Commands\Concerns\ResolvesUseCaseExceptionMessage;
use Illuminate\Console\Command;
use Media\Application\Cli\UseCase\UpdateYouTubeChannel\UpdateYouTubeChannelInputData;
use Media\Application\Cli\UseCase\UpdateYouTubeChannel\UpdateYouTubeChannelUseCase;
use Override;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\UseCase\Exceptions\UseCaseException;

class UpdateYouTubeChannelCommand extends Command
{
    use ResolvesUseCaseExceptionMessage;

    #[Override]
    protected $signature = 'media:youtube-channel:update {channelId} {name}';

    #[Override]
    protected $description = 'インポート対象の YouTube チャンネルの名前を変更する';

    public function handle(UpdateYouTubeChannelUseCase $useCase): int
    {
        $name = $this->argument('name');

        if (mb_trim($name) === '') {
            $this->error('チャンネル名を入力してください');

            return Command::FAILURE;
        }

        try {
            $output = $useCase->handle(new UpdateYouTubeChannelInputData($this->argument('channelId'), $name));
        } catch (BusinessRuleViolationException|InvalidDomainException|UseCaseException $e) {
            $this->error($this->resolveExceptionMessage($e));

            return Command::FAILURE;
        }

        $channel = $output->channel;

        $this->info(sprintf('チャンネルを更新しました: %s (%s)', $channel->name->value, $channel->channelId->value));

        return Command::SUCCESS;
    }
}
