<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Event;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Event\DeletePresenter;
use Event\Application\Admin\UseCase\Delete\DeleteInputData;
use Event\Application\Admin\UseCase\Delete\DeleteUseCase;
use Illuminate\Http\JsonResponse;

class DeleteEventController extends Controller
{
    public function __construct(
        private readonly DeleteUseCase $useCase,
        private readonly DeletePresenter $presenter,
    ) {
    }

    public function handle(string $eventId): JsonResponse
    {
        $this->useCase->handle(new DeleteInputData($eventId));

        return $this->presenter->present();
    }
}
