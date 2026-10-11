<?php

declare(strict_types=1);

namespace App\Console\Commands\Media;

use Illuminate\Console\Command;
use Media\Application\Cli\UseCase\ImportYouTube\ImportYouTubeInputData;
use Media\Application\Cli\UseCase\ImportYouTube\ImportYouTubeUseCase;
use Override;

class ImportYouTubeCommand extends Command
{
    #[Override]
    protected $signature = 'media:youtube:import';

    #[Override]
    protected $description = '登録済みの YouTube チャンネルから動画をメディアとして取り込む';

    public function handle(ImportYouTubeUseCase $useCase): int
    {
        $output = $useCase->handle(new ImportYouTubeInputData());

        if ($output->results === []) {
            $this->warn('チャンネルが登録されていません');

            return Command::SUCCESS;
        }

        foreach ($output->results as $channelResult) {
            if (! $channelResult->channelFound) {
                $this->warn(sprintf('%s: チャンネルが見つかりませんでした (%s)', $channelResult->channel->name->value, $channelResult->channel->channelId->value));

                continue;
            }

            $this->info(sprintf('%s: %d 件取り込みました', $channelResult->channel->name->value, $channelResult->importedCount));
        }

        return Command::SUCCESS;
    }
}
