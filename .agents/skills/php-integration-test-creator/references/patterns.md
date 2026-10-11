# PHP インテグレーションテストパターン

共通規約は [../php-unit-test-creator/references/common.md](../php-unit-test-creator/references/common.md) を正とする
Unit がある場合、Integration はハッピーパス中心でよい

## インスタンス化

```php
private function getInstance(): CreateUseCase
{
    return $this->app->make(CreateUseCase::class);
}
```

## データ準備と検証

```php
$this->storeSongs($this->createSong(/* ... */));

$result = $this->getInstance()->handle(new CreateInputData(/* ... */));

$this->assertSame('テスト楽曲', $result->song->title->value);
$this->assertDatabaseHas('songs', ['title' => 'テスト楽曲']);
```

`DatabaseTestCase` は各テスト後にロールバックする
