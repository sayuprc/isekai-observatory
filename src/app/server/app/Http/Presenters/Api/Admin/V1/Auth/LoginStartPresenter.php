<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use Auth\Application\Admin\UseCase\Login\LoginStartOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\LoginStartResponse;

class LoginStartPresenter
{
    public function present(LoginStartOutputData $output): JsonResponse
    {
        return response()->json(
            new LoginStartResponse()
                ->setAuthCeremonyId($output->authCeremonyId)
                ->setPublicKey((object)$output->publicKey),
            200,
        );
    }
}
