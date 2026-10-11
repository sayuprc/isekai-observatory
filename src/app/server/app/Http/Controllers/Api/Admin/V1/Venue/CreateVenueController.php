<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Venue;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Venue\CreatePresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Contracts\MapperInterface;
use Venue\Application\Admin\UseCase\Create\CreateInputData;
use Venue\Application\Admin\UseCase\Create\CreateUseCase;

class CreateVenueController extends Controller
{
    public function __construct(
        private readonly MapperInterface $mapper,
        private readonly CreateUseCase $useCase,
        private readonly CreatePresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        return $this->mapper->map(CreateInputData::class, $request->all())
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
