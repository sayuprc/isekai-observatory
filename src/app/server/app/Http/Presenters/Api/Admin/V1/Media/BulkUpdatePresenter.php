<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Media;

use Illuminate\Http\JsonResponse;
use Media\Application\Admin\UseCase\BulkUpdate\BulkUpdateOutputData;
use OpenAPI\Admin\Client\Model\MediaBulkUpdateResponse;

class BulkUpdatePresenter
{
    public function present(BulkUpdateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new MediaBulkUpdateResponse()->setUpdatedCount($outputData->updatedCount),
            200,
        );
    }
}
