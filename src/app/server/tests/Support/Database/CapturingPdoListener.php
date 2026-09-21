<?php

declare(strict_types=1);

namespace Tests\Support\Database;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOListenerInterface;
use Override;

/**
 * emonkak が実行した SQL を捕捉するテスト用リスナ
 *
 * emonkak は Laravel の Connection を経由せず PDO を直接叩くため、
 * DB::getQueryLog() では SQL を観測できない。FOR UPDATE などの発行を検証したいテストで使う
 */
final class CapturingPdoListener implements PDOListenerInterface
{
    /** @var list<string> */
    public array $queries = [];

    #[Override]
    public function onQuery(PDOInterface $pdo, string $queryString, array $bindings, float $time): void
    {
        $this->queries[] = $queryString;
    }

    #[Override]
    public function onBeginTransaction(PDOInterface $pdo): void
    {
    }

    #[Override]
    public function onRollback(PDOInterface $pdo): void
    {
    }

    #[Override]
    public function onCommit(PDOInterface $pdo): void
    {
    }
}
