<?php

declare(strict_types=1);

namespace App\Http\Middleware\Admin;

use App\Http\Responses\ApiError;
use Auth\Application\Admin\UseCase\Authenticate\AuthenticateInputData;
use Auth\Application\Admin\UseCase\Authenticate\AuthenticateUseCase;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    public function __construct(private readonly AuthenticateUseCase $useCase)
    {
    }

    /**
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $accessToken = $request->bearerToken();

        if (! is_string($accessToken)) {
            [$payload, $status] = ApiError::unauthenticated();

            return response()->json($payload, $status);
        }

        // 認証失敗は UseCase が UnauthenticatedException を投げ、例外ハンドラが 401 に変換する
        $this->useCase->handle(new AuthenticateInputData($accessToken));

        return $next($request);
    }
}
