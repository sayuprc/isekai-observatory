<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Override;
use RuntimeException;
use Tests\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    use DatabaseTransactions;

    private static bool $databaseCreated = false;

    private static ?string $testDatabase = null;

    #[Override]
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        $token = $_SERVER['TEST_TOKEN'] ?? null;

        if ($token === null) {
            return $app;
        }

        $_SERVER['LARAVEL_PARALLEL_TESTING_WITHOUT_DATABASES'] = true;

        if (! self::$databaseCreated) {
            self::$databaseCreated = true;
            self::$testDatabase = $this->createTestDatabase($app, (string)$token);
        }

        if (self::$testDatabase !== null) {
            $app->make('config')->set('database.connections.mysql.database', self::$testDatabase);
            DB::purge();
        }

        return $app;
    }

    private function createTestDatabase(Application $app, string $token): string
    {
        $config = $app->make('config');
        $baseDb = $config->get('database.connections.mysql.database');
        $testDb = "{$baseDb}_test_{$token}";
        $appUser = $config->get('database.connections.mysql.username');

        $rootMysql = $this->buildMysqlCommand($config);
        $rootMysqldump = $this->buildMysqldumpCommand($config, $baseDb);

        $result = exec("{$rootMysql} -e " . escapeshellarg("CREATE DATABASE IF NOT EXISTS `{$testDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_bin"));
        if ($result === false) {
            throw new RuntimeException('DB の生成に失敗した: ' . $testDb);
        }

        $result = exec("{$rootMysql} -e " . escapeshellarg("GRANT ALL PRIVILEGES ON `{$testDb}`.* TO '{$appUser}'@'%'"));
        if ($result === false) {
            throw new RuntimeException('権限の付与に失敗した: ' . $testDb);
        }

        $result = exec("{$rootMysqldump} | {$rootMysql} {$testDb}");
        if ($result === false) {
            throw new RuntimeException('DB dump からの復元に失敗した: ' . $testDb);
        }

        register_shutdown_function(static function () use ($rootMysql, $testDb): void {
            exec("{$rootMysql} -e " . escapeshellarg("DROP DATABASE IF EXISTS `{$testDb}`"));
        });

        return $testDb;
    }

    private function buildMysqlCommand(Repository $config): string
    {
        $host = $config->get('database.connections.mysql.host');
        $port = $config->get('database.connections.mysql.port');
        $username = $config->get('database.connections.mysql.root_username');
        $password = $config->get('database.connections.mysql.root_password');

        return sprintf(
            'mysql -h %s -P %s -u %s -p%s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
        );
    }

    private function buildMysqldumpCommand(Repository $config, string $database): string
    {
        $host = $config->get('database.connections.mysql.host');
        $port = $config->get('database.connections.mysql.port');
        $username = $config->get('database.connections.mysql.root_username');
        $password = $config->get('database.connections.mysql.root_password');

        return sprintf(
            'mysqldump --no-data -h %s -P %s -u %s -p%s %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
        );
    }
}
