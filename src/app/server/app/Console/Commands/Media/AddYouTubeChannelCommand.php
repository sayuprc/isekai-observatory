<?php

declare(strict_types=1);

namespace App\Console\Commands\Media;

use App\Console\Commands\Concerns\ResolvesUseCaseExceptionMessage;
use Illuminate\Console\Command;
use Media\Application\Cli\UseCase\AddYouTubeChannel\AddYouTubeChannelInputData;
use Media\Application\Cli\UseCase\AddYouTubeChannel\AddYouTubeChannelUseCase;
use Override;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\UseCase\Exceptions\UseCaseException;

class AddYouTubeChannelCommand extends Command
{
    use ResolvesUseCaseExceptionMessage;

    #[Override]
    protected $signature = 'media:youtube-channel:add {channelId} {name}';

    #[Override]
    protected $description = 'インポート対象の YouTube チャンネルを追加する';

    public function handle(AddYouTubeChannelUseCase $useCase): int
    {
        $name = $this->argument('name');

        if (mb_trim($name) === '') {
            $this->error('チャンネル名を入力してください');

            return Command::FAILURE;
        }

        try {
            $output = $useCase->handle(new AddYouTubeChannelInputData($this->argument('channelId'), $name));
        } catch (BusinessRuleViolationException|InvalidDomainException|UseCaseException $e) {
            $this->error($this->resolveExceptionMessage($e));

            return Command::FAILURE;
        }

        $channel = $output->channel;

        $this->info(sprintf('チャンネルを追加しました: %s (%s)', $channel->name->value, $channel->channelId->value));

        return Command::SUCCESS;
    }
}
