<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Event;

use Event\Application\Admin\UseCase\Search\SearchOutputData;
use Illuminate\Http\JsonResponse;

class SearchPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(SearchOutputData $outputData): JsonResponse
    {
        return response()->json(
            [
                'events' => array_map($this->converter->toSummary(...), $outputData->events),
                'maxPage' => $outputData->maxPage,
            ],
            200,
        );
    }
}
