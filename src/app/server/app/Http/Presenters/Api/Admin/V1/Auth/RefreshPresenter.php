<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use Auth\Application\Admin\UseCase\Refresh\RefreshOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\RefreshTokenResponse;

class RefreshPresenter
{
    public function present(RefreshOutputData $output): JsonResponse
    {
        return response()->json(
            new RefreshTokenResponse()
                ->setAccessToken($output->accessToken->jwt->value)
                ->setRefreshTokenId($output->refreshTokenId)
                ->setRefreshToken($output->plainRefreshToken),
            200,
        );
    }
}
