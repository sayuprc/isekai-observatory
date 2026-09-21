<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Song;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\SongSearchResponse;
use OpenAPI\Admin\Client\Model\SongSummary as OpenApiSongSummary;
use OpenAPI\Admin\Client\Model\SongType as OpenApiSongType;
use OpenAPI\Admin\Client\Model\SongTypeValue;
use Song\Application\Admin\Query\SongSummary;
use Song\Application\Admin\UseCase\Search\SearchOutputData;
use Song\Domain\Models\SongType;

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
            ->setType($this->toOpenApiSongType($song->type))
            ->setIsDisplay($song->isDisplay)
            ->setOrderNo($song->orderNo);
    }

    private function toOpenApiSongType(SongType $type): OpenApiSongType
    {
        return new OpenApiSongType()
            ->setName($type->getName())
            ->setValue(SongTypeValue::from($type->value));
    }
}
