<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use Auth\Application\Admin\UseCase\Recovery\RecoveryStartOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\RecoveryStartResponse;

class RecoveryStartPresenter
{
    public function present(RecoveryStartOutputData $output): JsonResponse
    {
        return response()->json(
            new RecoveryStartResponse()
                ->setAuthCeremonyId($output->authCeremonyId)
                ->setPublicKey((object)$output->publicKey),
            200,
        );
    }
}
