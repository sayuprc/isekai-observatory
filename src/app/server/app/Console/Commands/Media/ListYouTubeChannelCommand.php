<?php

declare(strict_types=1);

namespace App\Console\Commands\Media;

use Illuminate\Console\Command;
use Media\Application\Cli\UseCase\ListYouTubeChannel\ListYouTubeChannelInputData;
use Media\Application\Cli\UseCase\ListYouTubeChannel\ListYouTubeChannelUseCase;
use Media\Domain\Models\YouTubeChannel\YouTubeChannel;
use Override;

class ListYouTubeChannelCommand extends Command
{
    #[Override]
    protected $signature = 'media:youtube-channel:list';

    #[Override]
    protected $description = 'インポート対象の YouTube チャンネルを一覧表示する';

    public function handle(ListYouTubeChannelUseCase $useCase): int
    {
        $channels = $useCase->handle(new ListYouTubeChannelInputData())->channels;

        if ($channels === []) {
            $this->warn('チャンネルが登録されていません');

            return Command::SUCCESS;
        }

        $this->table(
            ['チャンネルID', 'チャンネル名'],
            array_map(
                static fn (YouTubeChannel $channel): array => [$channel->channelId->value, $channel->name->value],
                $channels,
            ),
        );

        return Command::SUCCESS;
    }
}
