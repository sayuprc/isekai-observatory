<?php

declare(strict_types=1);

namespace Tests\Integration\Support\Infrastructures\Database;

use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Support\Infrastructures\Database\QueryFactory;
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
}
