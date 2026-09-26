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
use Support\Domain\SearchCriteria\PerPage;

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

    /**
     * 管理画面の検索一覧で、指定ページの範囲だけを取るよう LIMIT / OFFSET を付ける
     */
    public function paginate(SelectBuilder $query, int $page, PerPage $perPage): SelectBuilder
    {
        return $query
            ->limit($perPage->value)
            ->offset(($page - 1) * $perPage->value);
    }

    /**
     * 検索条件に合う件数を数え、1 ページの件数から最大ページ数を求める
     */
    public function maxPage(SelectBuilder $query, PerPage $perPage): int
    {
        $count = Row::intValue($query->aggregate($this->pdo, 'COUNT(*)'));

        return (int)ceil($count / $perPage->value);
    }

    /**
     * 複数行をまとめて INSERT する。行が空なら何もしない
     *
     * @param list<string>            $columns
     * @param array<int, list<mixed>> $rows
     */
    public function insertRows(string $table, array $columns, array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $this->insert()
            ->into($table, $columns)
            ->values(...$rows)
            ->execute($this->pdo);
    }

    /**
     * 同じキー列を持つ複数テーブルから、そのキーの行を削除する
     *
     * 集約の子テーブルを洗い替えるときに使う
     *
     * @param list<string> $tables
     */
    public function deleteFromTables(array $tables, string $column, mixed $value): void
    {
        foreach ($tables as $table) {
            $this->delete()
                ->from($table)
                ->where($column, '=', $value)
                ->execute($this->pdo);
        }
    }

    public function pdo(): PDOInterface
    {
        return $this->pdo;
    }
}
