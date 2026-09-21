<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Media;

use Illuminate\Http\JsonResponse;

class DeletePresenter
{
    public function present(): JsonResponse
    {
        return response()->json(status: 204);
    }
}
