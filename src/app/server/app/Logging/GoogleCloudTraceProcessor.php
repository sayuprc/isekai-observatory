<?php

declare(strict_types=1);

namespace App\Logging;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

/**
 * Cloud Run の `X-Cloud-Trace-Context` を Cloud Logging のトレースフィールドへ変換する
 *
 * 付与するフィールド({@see GoogleCloudLoggingFormatter} がトップレベルへ引き上げる):
 * - logging.googleapis.com/trace         : projects/<project>/traces/<traceId>
 * - logging.googleapis.com/trace_sampled : サンプリング対象か
 *
 * spanId は header 上は uint64 の 10 進数で、Cloud Logging が要求する 16 桁 hex への
 * 変換が bcmath/gmp 無しでは安全に行えないため付与しない。ログのリクエスト単位の
 * グルーピングには trace のみで足りる
 *
 * チャンネルは worker モードで使い回されるため、ヘッダはログ出力時に遅延解決する
 */
final class GoogleCloudTraceProcessor implements ProcessorInterface
{
    public function __construct(private readonly string $projectId)
    {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        if ($this->projectId === '') {
            return $record;
        }

        $fields = $this->resolveTraceFields($this->traceHeader());

        if ($fields === []) {
            return $record;
        }

        return $record->with(extra: [...$record->extra, ...$fields]);
    }

    /**
     * `X-Cloud-Trace-Context` (`TRACE_ID/SPAN_ID;o=OPTIONS`) を解析する
     *
     * @return array<string, string|bool>
     */
    private function resolveTraceFields(?string $header): array
    {
        if ($header === null || $header === '') {
            return [];
        }

        if (preg_match('#^(?<trace>[0-9a-fA-F]+)#', $header, $matches) !== 1) {
            return [];
        }

        $fields = [
            'logging.googleapis.com/trace' => sprintf('projects/%s/traces/%s', $this->projectId, $matches['trace']),
        ];

        if (preg_match('#;o=(?<sampled>[01])#', $header, $option) === 1) {
            $fields['logging.googleapis.com/trace_sampled'] = $option['sampled'] === '1';
        }

        return $fields;
    }

    private function traceHeader(): ?string
    {
        $container = Container::getInstance();

        if (! $container->bound('request')) {
            return null;
        }

        $header = $container->make(Request::class)->header('X-Cloud-Trace-Context');

        return is_string($header) ? $header : null;
    }
}
