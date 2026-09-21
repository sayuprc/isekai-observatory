<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use Auth\Application\Admin\UseCase\RegisterStart\RegisterStartOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\RegisterStartResponse;

class RegisterStartPresenter
{
    public function present(RegisterStartOutputData $output): JsonResponse
    {
        return response()->json(
            new RegisterStartResponse()
                ->setAuthCeremonyId($output->authCeremonyId)
                ->setPublicKey((object)$output->publicKey),
            200,
        );
    }
}
