<?php

declare(strict_types=1);

namespace Support\Infrastructures\Database;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\DeleteBuilder;
use Emonkak\Orm\Fetcher\ArrayFetcher;
use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\InsertBuilder;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\UpdateBuilder;

/**
 * emonkak/orm のビルダと Fetcher を生成する共通ファクトリ
 *
 * Repository / QueryService は本クラスを注入し、Grammar や PDO の取り回しを直接持たない
 * PDO は Laravel の Connection を共有する(DatabaseServiceProvider 参照)
 */
final readonly class QueryFactory
{
    public function __construct(
        private GrammarInterface $grammar,
        private PDOInterface $pdo,
    ) {
    }

    public function select(): SelectBuilder
    {
        return new SelectBuilder($this->grammar);
    }

    public function insert(): InsertBuilder
    {
        return new InsertBuilder($this->grammar);
    }

    public function update(): UpdateBuilder
    {
        return new UpdateBuilder($this->grammar);
    }

    public function delete(): DeleteBuilder
    {
        return new DeleteBuilder($this->grammar);
    }

    public function arrayFetcher(): ArrayFetcher
    {
        return new ArrayFetcher($this->pdo);
    }

    /**
     * SELECT を実行して行を連想配列の一覧として取得する
     *
     * @return list<array<string, mixed>>
     */
    public function fetchAll(QueryBuilderInterface $query): array
    {
        /** @var array<array<string, mixed>> $rows */
        $rows = $this->arrayFetcher()->fetch($query)->toArray();

        return array_values($rows);
    }

    public function pdo(): PDOInterface
    {
        return $this->pdo;
    }
}
