<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Song;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\SongSearchResponse;
use OpenAPI\Admin\Client\Model\SongSummary as OpenApiSongSummary;
use OpenAPI\Admin\Client\Model\SongTypeValue;
use Song\Application\Admin\Query\SongSummary;
use Song\Application\Admin\UseCase\Search\SearchOutputData;

class SearchPresenter
{
    public function present(SearchOutputData $outputData): JsonResponse
    {
        return response()->json(
            new SongSearchResponse()
                ->setSongs(array_map($this->toOpenApiSongSummary(...), $outputData->songs))
                ->setMaxPage($outputData->maxPage),
            200,
        );
    }

    private function toOpenApiSongSummary(SongSummary $song): OpenApiSongSummary
    {
        return new OpenApiSongSummary()
            ->setSongId($song->songId)
            ->setTitle($song->title)
            ->setType(SongTypeValue::from($song->type->value))
            ->setIsDisplay($song->isDisplay)
            ->setOrderNo($song->orderNo)
            ->setPerformanceCount($song->performanceCount)
            ->setMediaCount($song->mediaCount)
            ->setPersonCount($song->personCount)
            ->setReleaseCount($song->releaseCount);
    }
}
