# PHP ユニットテストパターン

共通規約は [common.md](common.md) を正とする。ここでは Unit 固有のパターンだけを書く

## 場所と基底

- `src/app/server/tests/Unit/` (対象クラスのディレクトリ構造に合わせる)
- 基底は `Tests\TestCase`

## Mockery

```php
private MockInterface&RepositoryInterface $repository;

protected function setUp(): void
{
    parent::setUp();
    $this->repository = Mockery::mock(RepositoryInterface::class);
}
```

```php
$this->repository->shouldReceive('find')
    ->with($id)
    ->andReturn($model)
    ->once();
```

## getInstance

```php
private function getInstance(): CreateUseCase
{
    return new CreateUseCase($this->service);
}
```

## 例

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Admin\UseCase;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\UseCase\Create\CreateUseCase;
use Song\Domain\Services\SongIntegrityService;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class CreateUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&SongIntegrityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = Mockery::mock(SongIntegrityService::class);
    }

    #[Test]
    public function create(): void
    {
        // ...
    }

    private function getInstance(): CreateUseCase
    {
        return new CreateUseCase($this->service);
    }
}
```
