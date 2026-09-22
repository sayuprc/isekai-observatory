<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Event;

use App\Http\Presenters\Api\Admin\V1\Event\Converter;
use Event\Application\Admin\UseCase\Search\SearchInputData;
use Event\Application\Admin\UseCase\Search\SearchUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Domain\SearchCriteria\Order;
use Support\Domain\SearchCriteria\PerPage;

class SearchEventController
{
    public function __construct(
        private readonly SearchUseCase $useCase,
        private readonly Converter $converter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $output = $this->useCase->handle(new SearchInputData(
            $request->query('title'),
            $request->query('type') === null ? null : (int)$request->query('type'),
            $request->query('status') === null ? null : (int)$request->query('status'),
            $request->query('is_display') === null ? null : filter_var($request->query('is_display'), FILTER_VALIDATE_BOOLEAN),
            (string)$request->query('sort', 'schedule'),
            Order::tryFrom((string)$request->query('order', 'asc')) ?? Order::Asc,
            max(1, (int)$request->query('page', 1)),
            PerPage::tryFrom((int)$request->query('per_page', 25)) ?? PerPage::TwentyFive,
        ));

        return response()->json(['events' => array_map($this->converter->toSummary(...), $output->events), 'maxPage' => $output->maxPage]);
    }
}
