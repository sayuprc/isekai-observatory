<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Venue;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Venue\DeletePresenter;
use Illuminate\Http\JsonResponse;
use Venue\Application\Admin\UseCase\Delete\DeleteInputData;
use Venue\Application\Admin\UseCase\Delete\DeleteUseCase;

class DeleteVenueController extends Controller
{
    public function __construct(
        private readonly DeleteUseCase $useCase,
        private readonly DeletePresenter $presenter,
    ) {
    }

    public function handle(string $venueId): JsonResponse
    {
        $this->useCase->handle(new DeleteInputData($venueId));

        return $this->presenter->present();
    }
}
