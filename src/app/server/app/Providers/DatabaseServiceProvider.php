<?php

declare(strict_types=1);

namespace App\Providers;

use Emonkak\Database\PDOAdapter;
use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Grammar\DefaultGrammar;
use Emonkak\Orm\Grammar\GrammarInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Override;

class DatabaseServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->app->bind(GrammarInterface::class, DefaultGrammar::class);

        // emonkak/orm は Laravel の Connection が管理する PDO を共有する
        // 接続設定は config/database.php に一元化され、DB::transaction が emonkak の
        // クエリも包む(テストの DatabaseTransactions も同じ接続でロールバックする)
        $this->app->bind(
            PDOInterface::class,
            static fn (): PDOInterface => new PDOAdapter(DB::connection()->getPdo()),
        );
    }
}
