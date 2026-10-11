<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Auth;

use Auth\Application\Admin\UseCase\RecoveryCode\Generate\GenerateRecoveryCodesOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\GenerateRecoveryCodesResponse;

class GenerateRecoveryCodesPresenter
{
    public function present(GenerateRecoveryCodesOutputData $output): JsonResponse
    {
        return response()->json(
            new GenerateRecoveryCodesResponse()->setRecoveryCodes($output->plainCodes),
            200,
        );
    }
}
