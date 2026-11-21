<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Api\Admin\V1\Auth\RecoveryFinishPresenter;
use Auth\Application\Admin\Recovery\RecoveryFinishInputData;
use Auth\Application\Admin\Recovery\RecoveryFinishUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Support\Contracts\MapperInterface;

final readonly class RecoveryFinishController extends Controller
{
    public function __construct(
        private MapperInterface $mapper,
        private RecoveryFinishUseCase $useCase,
        private RecoveryFinishPresenter $presenter,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        // credential は中身を解釈せず PasskeyAuthenticator まで運ぶので、JSON 文字列に戻して渡す
        $source = [
            ...$request->except('credential'),
            'credentialJson' => json_encode($request->input('credential'), JSON_THROW_ON_ERROR),
        ];

        return $this->mapper->map(RecoveryFinishInputData::class, $source)
            |> $this->useCase->handle(...)
            |> $this->presenter->present(...);
    }
}
