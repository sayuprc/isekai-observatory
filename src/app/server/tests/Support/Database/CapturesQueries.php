<?php

declare(strict_types=1);

namespace Tests\Support\Database;

use Emonkak\Database\ListenableConnection;
use Emonkak\Database\PDOAdapter;
use Emonkak\Database\PDOInterface;
use Illuminate\Support\Facades\DB;

/**
 * 以降にコンテナから解決される Repository が emonkak で発行する SQL を捕捉できるようにする
 *
 * Laravel の Connection と同一の PDO を共有するため、DatabaseTransactions による
 * ロールバックも従来どおり機能する
 */
trait CapturesQueries
{
    private ?CapturingPdoListener $queryListener = null;

    private function startCapturingQueries(): void
    {
        $listener = new CapturingPdoListener();

        $connection = new ListenableConnection(new PDOAdapter(DB::connection()->getPdo()));
        $connection->addListaner($listener);

        $this->app->instance(PDOInterface::class, $connection);

        $this->queryListener = $listener;
    }

    /**
     * @return list<string>
     */
    private function capturedQueries(): array
    {
        return $this->queryListener?->queries ?? [];
    }
}
