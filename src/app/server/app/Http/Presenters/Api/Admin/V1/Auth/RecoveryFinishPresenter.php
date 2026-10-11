<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use Auth\Application\Admin\UseCase\Recovery\RecoveryFinishOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\RecoveryFinishResponse;

class RecoveryFinishPresenter
{
    public function present(RecoveryFinishOutputData $output): JsonResponse
    {
        return response()->json(
            new RecoveryFinishResponse()
                ->setAccessToken($output->accessToken->jwt->value)
                ->setRefreshTokenId($output->refreshTokenId)
                ->setRefreshToken($output->plainRefreshToken),
            200,
        );
    }
}
