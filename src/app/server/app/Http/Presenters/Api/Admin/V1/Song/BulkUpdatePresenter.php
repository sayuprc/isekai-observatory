<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Song;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\SongBulkUpdateResponse;
use Song\Application\Admin\UseCase\BulkUpdate\BulkUpdateOutputData;

class BulkUpdatePresenter
{
    public function present(BulkUpdateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new SongBulkUpdateResponse()->setUpdatedCount($outputData->updatedCount),
            200,
        );
    }
}
