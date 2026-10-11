<?php

declare(strict_types=1);

namespace Tests\Integration\Song\Application\Admin\UseCase\Tag;

use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\UseCase\Tag\Get\GetInputData;
use Song\Application\Admin\UseCase\Tag\Get\GetUseCase;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class GetUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function getSongTag(): void
    {
        $uuid = $this->generateUuid();

        $this->storeSongTags($this->createSongTag($uuid, 'テストタグA', 1));

        $result = $this->getInstance()->handle(new GetInputData($uuid));

        $response = $result;

        $this->assertSame($uuid, $response->tag->songTagId->value);
        $this->assertSame('テストタグA', $response->tag->name->value);
        $this->assertSame(1, $response->tag->orderNo->value);
    }

    private function getInstance(): GetUseCase
    {
        $this->privilegedContext();

        return $this->app->make(GetUseCase::class);
    }
}
