<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use Auth\Application\Admin\UseCase\Login\LoginFinishOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\LoginFinishResponse;

class LoginFinishPresenter
{
    public function present(LoginFinishOutputData $output): JsonResponse
    {
        return response()->json(
            new LoginFinishResponse()
                ->setAccessToken($output->accessToken->jwt->value)
                ->setRefreshTokenId($output->refreshTokenId)
                ->setRefreshToken($output->plainRefreshToken),
            200,
        );
    }
}
