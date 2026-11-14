<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Venue;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Venue\UpdatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Contracts\MapperInterface;
use Venue\Application\Admin\UseCase\Update\UpdateInputData;
use Venue\Application\Admin\UseCase\Update\UpdateUseCase;

final readonly class UpdateVenueController extends Controller
{
    public function __construct(
        private MapperInterface $mapper,
        private UpdateUseCase $useCase,
        private UpdatePresenter $presenter,
    ) {
    }

    public function handle(string $venueId, Request $request): JsonResponse
    {
        return $this->mapper->map(UpdateInputData::class, [...$request->all(), 'venueId' => $venueId])
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
