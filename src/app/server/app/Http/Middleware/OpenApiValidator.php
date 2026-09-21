<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\OpenApi\BodyErrorCollector;
use App\Http\Responses\ApiError;
use App\OpenApi\SchemaProvider;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use League\OpenAPIValidation\PSR7\Exception\Validation\InvalidSecurity;
use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\ResponseValidator;
use League\OpenAPIValidation\PSR7\RoutedServerRequestValidator;
use League\OpenAPIValidation\Schema\Exception\FormatMismatch;
use League\OpenAPIValidation\Schema\Exception\SchemaMismatch;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Response;

abstract class OpenApiValidator
{
    private readonly PsrHttpFactory $psrHttpFactory;

    private readonly RoutedServerRequestValidator $requestValidator;

    private readonly ResponseValidator $responseValidator;

    public function __construct(
        private readonly LoggerInterface $logger,
        SchemaProvider $schemaProvider,
        Psr17Factory $psr17Factory,
    ) {
        $schema = $schemaProvider->provide($this->getPath());

        $this->requestValidator = new RoutedServerRequestValidator($schema);
        $this->responseValidator = new ResponseValidator($schema);

        $this->psrHttpFactory = new PsrHttpFactory(
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
        );
    }

    abstract protected function getPath(): string;

    /**
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $psrRequest = $this->psrHttpFactory->createRequest($request);
        $operationAddress = $this->resolveOperationAddress($request);

        try {
            $this->requestValidator->validate($operationAddress, $psrRequest);
        } catch (InvalidSecurity) {
            [$payload, $status] = ApiError::unauthenticated();

            return response()->json($payload, $status);
        } catch (ValidationFailed $e) {
            return $this->handleValidationFailed($e, $psrRequest, $operationAddress);
        }

        $response = $next($request);

        $psrResponse = $this->psrHttpFactory->createResponse($response);

        try {
            $this->responseValidator->validate($operationAddress, $psrResponse);
        } catch (ValidationFailed $e) {
            $this->logger->error('レスポンスバリデーションエラー', [
                'content' => $response->getContent(),
            ]);

            // サーバーレスポンス不整合はサーバー内部の問題なので 500 として返す
            [$payload, $status] = ApiError::internalError();

            return response()->json($payload, $status);
        }

        return $response;
    }

    private function resolveOperationAddress(Request $request): OperationAddress
    {
        $route = $request->route();
        $path = is_null($route) ? ltrim($request->getPathInfo(), '/') : $route->uri();
        $path = preg_replace($this->getRoutePrefixPattern(), '', $path) ?? $path;

        return new OperationAddress('/' . ltrim($path, '/'), strtolower($request->getMethod()));
    }

    abstract protected function getRoutePrefixPattern(): string;

    private function handleValidationFailed(
        ValidationFailed $exception,
        ServerRequestInterface $psrRequest,
        OperationAddress $operationAddress,
    ): JsonResponse {
        // 契約スキーマに対する body の全違反を一括報告する (ADR-0014)
        // body 以外 (query / path 等) の違反は従来どおり先頭 1 件の報告に落ちる
        $details = new BodyErrorCollector($this->getPath())->collect($operationAddress, (string)$psrRequest->getBody());

        if ($details !== []) {
            [$payload, $status] = ApiError::validationFailed($details);

            return response()->json($payload, $status);
        }

        $previous = $exception->getPrevious();

        [$field, $message] = $previous instanceof SchemaMismatch
            ? $this->formatSchemaMismatch($previous)
            : ['', '予期せぬエラー'];

        [$payload, $status] = ApiError::validationFailed([$field => [$message]]);

        return response()->json($payload, $status);
    }

    /**
     * @return array{0: string, 1: string} field とメッセージの組
     */
    private function formatSchemaMismatch(SchemaMismatch $exception): array
    {
        $breadcrumb = $exception->dataBreadCrumb();

        if (is_null($breadcrumb)) {
            return ['', '予期せぬエラー'];
        }

        /** @var array<string> */
        $chain = $breadcrumb->buildChain();
        $field = implode('/', $chain);

        $message = match (true) {
            $exception instanceof FormatMismatch => sprintf('The value does not match the expected format: %s.', $exception->format()),
            default => $exception->getMessage(),
        };

        return [$field, $message];
    }
}
