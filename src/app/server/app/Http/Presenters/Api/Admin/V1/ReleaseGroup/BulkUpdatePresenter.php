<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\ReleaseGroup;

use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\ReleaseGroupBulkUpdateResponse;
use Release\Application\Admin\UseCase\Group\BulkUpdate\BulkUpdateOutputData;

class BulkUpdatePresenter
{
    public function present(BulkUpdateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new ReleaseGroupBulkUpdateResponse()->setUpdatedCount($outputData->updatedCount),
            200,
        );
    }
}
