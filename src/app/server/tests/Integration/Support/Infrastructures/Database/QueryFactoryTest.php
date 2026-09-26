<?php

declare(strict_types=1);

namespace Tests\Integration\Support\Infrastructures\Database;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Support\Domain\SearchCriteria\PerPage;
use Support\Infrastructures\Database\QueryFactory;
use Support\Infrastructures\Database\Row;
use Tests\Support\DatabaseTestCase;

class QueryFactoryTest extends DatabaseTestCase
{
    #[Test]
    public function sharesTheSamePdoInstanceAsTheLaravelConnection(): void
    {
        $factory = $this->app->make(QueryFactory::class);

        // emonkak が Laravel の Connection と同一の PDO を共有していることを保証する
        // これが崩れると DB::transaction / DatabaseTransactions が emonkak のクエリを
        // 包めなくなる(接続が分断される)
        $this->assertSame(DB::connection()->getPdo(), $factory->pdo()->getPdo());
    }

    #[Test]
    public function executesSelectThroughEmonkak(): void
    {
        $factory = $this->app->make(QueryFactory::class);

        $rows = $factory->select()
            ->select('1', 'one')
            ->getResult($factory->arrayFetcher())
            ->toArray();

        $this->assertCount(1, $rows);
        $this->assertEquals(1, $rows[0]['one']);
    }

    #[Test]
    public function insertRowsInsertsAllRowsAndSkipsEmptyInput(): void
    {
        $factory = $this->createFactoryWithTables();

        $factory->insertRows('tmp_children_a', ['parent_id', 'value'], []);
        $factory->insertRows('tmp_children_a', ['parent_id', 'value'], [[1, 'a'], [1, 'b'], [2, 'c']]);

        $this->assertSame(3, $this->countRows('tmp_children_a'));
    }

    #[Test]
    public function deleteFromTablesDeletesOnlyRowsOfTheKey(): void
    {
        $factory = $this->createFactoryWithTables();
        $factory->insertRows('tmp_children_a', ['parent_id', 'value'], [[1, 'a'], [2, 'b']]);
        $factory->insertRows('tmp_children_b', ['parent_id', 'value'], [[1, 'c'], [2, 'd']]);

        $factory->deleteFromTables(['tmp_children_a', 'tmp_children_b'], 'parent_id', 1);

        $this->assertSame(1, $this->countRows('tmp_children_a'));
        $this->assertSame(1, $this->countRows('tmp_children_b'));
    }

    #[Test]
    public function paginateAndMaxPageFollowPerPage(): void
    {
        $factory = $this->createFactoryWithTables();
        $factory->insertRows(
            'tmp_children_a',
            ['parent_id', 'value'],
            array_map(static fn (int $i): array => [$i, "v{$i}"], range(1, 26)),
        );
        $query = $factory->select()->from('tmp_children_a');

        $secondPage = $factory->fetchAll(
            $factory->paginate($query->withSelect(['parent_id'])->orderBy('parent_id'), 2, PerPage::TwentyFive),
        );

        $this->assertSame([26], array_map(static fn (array $row): int => Row::int($row, 'parent_id'), $secondPage));
        $this->assertSame(2, $factory->maxPage($query, PerPage::TwentyFive));
        $this->assertSame(1, $factory->maxPage($query, PerPage::Fifty));
        $this->assertSame(0, $factory->maxPage($query->where('parent_id', '>', 100), PerPage::TwentyFive));
    }

    private function createFactoryWithTables(): QueryFactory
    {
        // 一時テーブルは暗黙コミットを起こさないため、テストのトランザクション内で使える
        foreach (['tmp_children_a', 'tmp_children_b'] as $table) {
            DB::statement("CREATE TEMPORARY TABLE {$table} (parent_id INT NOT NULL, value VARCHAR(10) NOT NULL)");
        }

        return $this->app->make(QueryFactory::class);
    }

    private function countRows(string $table): int
    {
        return DB::table($table)->count();
    }
}
