<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Event;

use Event\Application\Admin\UseCase\BulkUpdate\BulkUpdateOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\EventBulkUpdateResponse;

class BulkUpdatePresenter
{
    public function present(BulkUpdateOutputData $outputData): JsonResponse
    {
        return response()->json(
            new EventBulkUpdateResponse()->setUpdatedCount($outputData->updatedCount),
            200,
        );
    }
}
