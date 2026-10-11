# PHP フィーチャーテストパターン

共通規約は [../php-unit-test-creator/references/common.md](../php-unit-test-creator/references/common.md) を正とする

## API

```php
use Song\Route\SongRouteMap;

$response = $this->postJson(route(SongRouteMap::Create), [
    'title' => '曲名',
]);

$response->assertStatus(200)
    ->assertJson([ /* ... */ ]);
```

認証が必要なら `WithAuth` を使う

## Console

```php
$this->artisan('command:signature argument --option=value')
    ->expectsOutput('期待される出力メッセージ')
    ->assertSuccessful();
```

失敗系は `assertFailed()` と期待メッセージを組み合わせる

## データ準備

```php
$this->storeSongs($this->createSong(/* ... */));
// または
$this->app->make(SongRepository::class)->save($this->createSong(/* ... */));
```
