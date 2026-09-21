<?php

declare(strict_types=1);

namespace Tests\Unit\Song\Application\Admin\UseCase\Tag;

use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Song\Application\Admin\UseCase\Tag\Get\GetInputData;
use Song\Application\Admin\UseCase\Tag\Get\GetUseCase;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagRepositoryInterface;
use Support\UseCase\Exceptions\ResourceNotFoundException;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class GetUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&SongTagRepositoryInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(SongTagRepositoryInterface::class);
    }

    #[Test]
    public function getSongTag(): void
    {
        $this->repository->shouldReceive('find')
            ->withArgs(static fn (SongTagId $arg): bool => $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB')
            ->andReturn($this->createSongTag('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'テストタグA', 1))
            ->once();

        $result = $this->getInstance()->handle(new GetInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $response = $result;

        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $response->tag->songTagId->value);
        $this->assertSame('テストタグA', $response->tag->name->value);
        $this->assertSame(1, $response->tag->orderNo->value);
    }

    #[Test]
    public function failureGetSongTag(): void
    {
        $this->repository->shouldReceive('find')
            ->withArgs(static fn (SongTagId $arg): bool => $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB')
            ->andReturnNull()
            ->once();

        $this->expectException(ResourceNotFoundException::class);

        $result = $this->getInstance()->handle(new GetInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));
    }

    private function getInstance(): GetUseCase
    {
        return new GetUseCase(
            $this->authorizer(),
            $this->repository,
        );
    }
}
