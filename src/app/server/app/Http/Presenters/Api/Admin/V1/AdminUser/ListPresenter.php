<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\AdminUser;

use AdminUser\Application\Admin\UseCase\List\ListOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\AdminUserListResponse;

class ListPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(ListOutputData $outputData): JsonResponse
    {
        return response()->json(
            new AdminUserListResponse()->setAdminUsers(array_map($this->converter->toOpenApiAdminUser(...), $outputData->adminUsers)),
            200,
        );
    }
}
