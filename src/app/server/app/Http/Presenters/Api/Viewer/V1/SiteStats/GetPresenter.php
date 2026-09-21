<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Viewer\V1\SiteStats;

use Illuminate\Http\JsonResponse;
use OpenAPI\Viewer\Client\Model\SiteStatsResponse;
use SiteStats\Application\Viewer\UseCase\Get\GetOutputData;

class GetPresenter
{
    public function present(GetOutputData $outputData): JsonResponse
    {
        return response()->json(
            new SiteStatsResponse()
                ->setSongCount($outputData->songCount)
                ->setReleaseCount($outputData->releaseCount),
            200,
        );
    }
}
