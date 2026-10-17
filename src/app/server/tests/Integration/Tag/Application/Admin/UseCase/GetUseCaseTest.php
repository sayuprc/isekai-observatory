<?php

declare(strict_types=1);

namespace Tests\Integration\Tag\Application\Admin\UseCase;

use PHPUnit\Framework\Attributes\Test;
use Tag\Application\Admin\UseCase\Get\GetInputData;
use Tag\Application\Admin\UseCase\Get\GetUseCase;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class GetUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function getTag(): void
    {
        $uuid = $this->generateUuid();

        $this->storeTags($this->createTag($uuid, 'テストタグA', 1));

        $result = $this->getInstance()->handle(new GetInputData($uuid));

        $response = $result;

        $this->assertSame($uuid, $response->tag->tagId->value);
        $this->assertSame('テストタグA', $response->tag->name->value);
        $this->assertSame(1, $response->tag->orderNo->value);
    }

    private function getInstance(): GetUseCase
    {
        $this->privilegedContext();

        return $this->app->make(GetUseCase::class);
    }
}
