<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Venue;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Venue\GetPresenter;
use Illuminate\Http\JsonResponse;
use Venue\Application\Admin\UseCase\Get\GetInputData;
use Venue\Application\Admin\UseCase\Get\GetUseCase;

class GetVenueController extends Controller
{
    public function __construct(
        private readonly GetUseCase $useCase,
        private readonly GetPresenter $presenter,
    ) {
    }

    public function handle(string $venueId): JsonResponse
    {
        return $this->presenter->present($this->useCase->handle(new GetInputData($venueId)));
    }
}
