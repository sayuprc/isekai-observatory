<?php

declare(strict_types=1);

namespace Tests\Unit\Tag\Application\Admin\UseCase;

use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\Exceptions\ResourceNotFoundException;
use Tag\Application\Admin\UseCase\Get\GetInputData;
use Tag\Application\Admin\UseCase\Get\GetUseCase;
use Tag\Domain\Models\TagId;
use Tag\Domain\Models\TagRepositoryInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class GetUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TagRepositoryInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(TagRepositoryInterface::class);
    }

    #[Test]
    public function getTag(): void
    {
        $this->repository->shouldReceive('find')
            ->withArgs(static fn (TagId $arg): bool => $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB')
            ->andReturn($this->createTag('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', 'テストタグA', 1))
            ->once();

        $result = $this->getInstance()->handle(new GetInputData('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB'));

        $response = $result;

        $this->assertSame('BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB', $response->tag->tagId->value);
        $this->assertSame('テストタグA', $response->tag->name->value);
        $this->assertSame(1, $response->tag->orderNo->value);
    }

    #[Test]
    public function failureGetTag(): void
    {
        $this->repository->shouldReceive('find')
            ->withArgs(static fn (TagId $arg): bool => $arg->value === 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB')
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
