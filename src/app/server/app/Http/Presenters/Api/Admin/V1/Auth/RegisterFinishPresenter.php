<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use Auth\Application\Admin\UseCase\RegisterFinish\RegisterFinishOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\RegisterFinishResponse;

class RegisterFinishPresenter
{
    public function present(RegisterFinishOutputData $output): JsonResponse
    {
        return response()->json(
            new RegisterFinishResponse()
                ->setAccessToken($output->accessToken->jwt->value)
                ->setRefreshTokenId($output->refreshTokenId)
                ->setRefreshToken($output->plainRefreshToken),
            200,
        );
    }
}
