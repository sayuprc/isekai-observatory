<?php

declare(strict_types=1);

namespace App\Http\Presenters\Api\Admin\V1\Event;

use Event\Application\Admin\UseCase\Search\SearchOutputData;
use Illuminate\Http\JsonResponse;
use OpenAPI\Admin\Client\Model\EventSearchResponse;

class SearchPresenter
{
    public function __construct(private readonly Converter $converter)
    {
    }

    public function present(SearchOutputData $outputData): JsonResponse
    {
        return response()->json(
            new EventSearchResponse()
                ->setEvents(array_map($this->converter->toOpenApiEventSummary(...), $outputData->events))
                ->setMaxPage($outputData->maxPage),
            200,
        );
    }
}
