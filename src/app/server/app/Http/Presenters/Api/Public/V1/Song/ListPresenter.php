<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Public\V1\Song;

use Illuminate\Http\JsonResponse;
use OpenAPI\Public\Client\Model\Song as OpenApiSong;
use OpenAPI\Public\Client\Model\SongCredit as OpenApiSongCredit;
use OpenAPI\Public\Client\Model\SongCreditRoleValue;
use OpenAPI\Public\Client\Model\SongListResponse;
use OpenAPI\Public\Client\Model\SongTypeValue;
use Song\Application\Public\Query\SongCredit;
use Song\Application\Public\Query\SongListItem;
use Song\Application\Public\UseCase\List\ListOutputData;

class ListPresenter
{
    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new SongListResponse(['next_page_token' => $outputData->nextPageToken])
                ->setItems(array_map($this->toOpenApiSong(...), $outputData->songs)),
            200,
        );
    }

    private function toOpenApiSong(SongListItem $song): OpenApiSong
    {
        return new OpenApiSong()
            ->setSongId($song->songId)
            ->setTitle($song->title)
            ->setDescription($song->description)
            ->setType(SongTypeValue::from($song->type->value))
            ->setCredits(array_map($this->toOpenApiSongCredit(...), $song->credits))
            ->setMediaIds($song->mediaIds);
    }

    private function toOpenApiSongCredit(SongCredit $credit): OpenApiSongCredit
    {
        return new OpenApiSongCredit()
            ->setPersonId($credit->personId)
            ->setRole(SongCreditRoleValue::from($credit->role->value));
    }
}
