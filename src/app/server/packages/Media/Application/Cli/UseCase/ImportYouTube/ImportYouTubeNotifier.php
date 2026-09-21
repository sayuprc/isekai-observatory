<?php

declare(strict_types=1);

namespace Media\Application\Cli\UseCase\ImportYouTube;

use Support\Notification\Contracts\Action;
use Support\Notification\Contracts\Color;
use Support\Notification\Contracts\Embed\NotificationEmbed;
use Support\Notification\Contracts\Status;
use Support\Notification\NotificationService;
use Throwable;

readonly class ImportYouTubeNotifier
{
    /**
     * Discord webhook 1 メッセージあたりの embeds 上限
     *
     * @see https://discord.com/developers/docs/resources/channel#embed-object
     */
    private const int MAX_EMBEDS = 10;

    /**
     * Discord embed description の文字数上限
     */
    private const int MAX_DESCRIPTION_LENGTH = 4096;

    public function __construct(private NotificationService $notifier)
    {
    }

    /**
     * @param list<ChannelImportResult> $results
     */
    public function succeeded(array $results): void
    {
        $this->notifier->notice(
            Action::MediaYoutubeImport,
            Status::Succeeded,
            content: 'YouTube からの取り込みを実行しました',
            embeds: $this->buildSucceededEmbeds($results),
        );
    }

    public function failed(Throwable $e): void
    {
        $this->notifier->notice(
            Action::MediaYoutubeImport,
            Status::Failed,
            content: 'YouTube からの取り込みで例外が発生しました',
            embeds: [
                new NotificationEmbed(
                    title: $e->getMessage(),
                    color: Color::Error,
                ),
            ],
        );
    }

    /**
     * @param list<ChannelImportResult> $results
     *
     * @return list<NotificationEmbed>
     */
    private function buildSucceededEmbeds(array $results): array
    {
        if (count($results) <= self::MAX_EMBEDS) {
            return array_map(
                fn (ChannelImportResult $result): NotificationEmbed => $this->buildEmbed($result),
                $results,
            );
        }

        $visibleResults = array_slice($results, 0, self::MAX_EMBEDS - 1);
        $omittedCount = count($results) - count($visibleResults);

        $embeds = array_map(
            fn (ChannelImportResult $result): NotificationEmbed => $this->buildEmbed($result),
            $visibleResults,
        );
        $embeds[] = new NotificationEmbed(
            title: '一部の結果を省略しました',
            description: sprintf(
                'Discord の embeds 上限 (%d件) のため、他 %d チャンネル分の結果は省略しています',
                self::MAX_EMBEDS,
                $omittedCount,
            ),
            color: Color::Warning,
        );

        return $embeds;
    }

    private function buildEmbed(ChannelImportResult $result): NotificationEmbed
    {
        if ($result->channelFound) {
            return new NotificationEmbed(
                title: sprintf('%s の取り込みが完了', $result->channel->name->value),
                description: $this->buildSuccessDescription($result),
                color: $result->importedCount === 0 ? Color::Warning : Color::Success,
                url: $this->buildChannelUrl($result->channel->channelId->value),
            );
        }

        return new NotificationEmbed(
            title: sprintf('%s に失敗', $result->channel->name->value),
            description: 'チャンネルが見つかりませんでした',
            color: Color::Error,
            url: $this->buildChannelUrl($result->channel->channelId->value),
        );
    }

    private function buildSuccessDescription(ChannelImportResult $result): string
    {
        $header = sprintf('取り込み件数: %d', $result->importedCount);

        if ($result->importedVideos === []) {
            return $header;
        }

        $lines = [$header, ''];
        $includedCount = 0;

        foreach ($result->importedVideos as $index => $video) {
            $line = $this->formatVideoLine($video);
            $candidateLines = [...$lines, $line];
            $candidate = implode("\n", $candidateLines);
            $remainingAfterThis = count($result->importedVideos) - $index - 1;

            if ($remainingAfterThis > 0) {
                $withOmissionNotice = $candidate . "\n\n" . $this->buildDescriptionOmissionNotice($remainingAfterThis);

                if (mb_strlen($withOmissionNotice) > self::MAX_DESCRIPTION_LENGTH) {
                    break;
                }
            } elseif (mb_strlen($candidate) > self::MAX_DESCRIPTION_LENGTH) {
                break;
            }

            $lines = $candidateLines;
            $includedCount++;
        }

        $omittedCount = count($result->importedVideos) - $includedCount;

        if ($omittedCount > 0) {
            $lines[] = '';
            $lines[] = $this->buildDescriptionOmissionNotice($omittedCount);
        }

        return implode("\n", $lines);
    }

    private function formatVideoLine(ImportedVideo $video): string
    {
        $title = str_replace(['[', ']'], ['［', '］'], $video->title);

        return sprintf('- [%s](%s)', $title, $video->url);
    }

    private function buildDescriptionOmissionNotice(int $omittedCount): string
    {
        return sprintf(
            '…他 %d 件のリンクは Discord の description 上限 (%d文字) のため省略しています',
            $omittedCount,
            self::MAX_DESCRIPTION_LENGTH,
        );
    }

    private function buildChannelUrl(string $channelId): string
    {
        return sprintf('https://www.youtube.com/channel/%s', $channelId);
    }
}
