<?php

declare(strict_types=1);

namespace Tests\Unit\Tag\Application\Admin\UseCase;

use Closure;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\Exceptions\ResourceNotFoundException;
use Tag\Application\Admin\UseCase\Update\UpdateInputData;
use Tag\Application\Admin\UseCase\Update\UpdateUseCase;
use Tag\Domain\Models\Tag;
use Tag\Domain\Models\TagId;
use Tag\Domain\Models\TagRepositoryInterface;
use Tag\Domain\Services\TagIntegrityService;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class UpdateUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&TagRepositoryInterface $repository;

    private MockInterface&TagIntegrityService $service;

    private AuditLogRecorderInterface&MockInterface $recorder;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->repository = Mockery::mock(TagRepositoryInterface::class);
        $this->service = Mockery::mock(TagIntegrityService::class);
        $this->recorder = Mockery::mock(AuditLogRecorderInterface::class);
        $this->recorder->shouldReceive('record')->byDefault();
    }

    #[Test]
    public function editTag(): void
    {
        $tagId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = 'テストタグA';
        $orderNo = 1;

        $this->transaction->shouldReceive('scope')
            ->withArgs(static fn (Closure $_) => true)
            ->andReturnUsing(static fn (Closure $arg) => $arg())
            ->once();

        $this->repository->shouldReceive('find')
            ->withArgs(static fn (TagId $arg): bool => $arg->value === $tagId)
            ->andReturn($this->createTag($tagId, '旧タグ', 10))
            ->once();

        $this->service->shouldReceive('prepareForUpdate')
            ->with($tagId, $name, $orderNo)
            ->andReturn($tag = $this->createTag($tagId, $name, $orderNo))
            ->once();

        $this->repository->shouldReceive('save')
            ->withArgs(
                static fn (Tag $arg): bool => $arg->tagId->value === $tagId
                    && $arg->name->value === $name
                    && $arg->orderNo->value === $orderNo,
            )
            ->andReturn($tag)
            ->once();

        $result = $this->getInstance()->handle(new UpdateInputData($tagId, $name, $orderNo));
    }

    #[Test]
    public function editFailsIfValidationErrorOccurs(): void
    {
        $tagId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $name = 'テストタグA';
        $orderNo = 1;

        $this->transaction->shouldReceive('scope')
            ->withArgs(static fn (Closure $_) => true)
            ->andReturnUsing(static fn (Closure $arg) => $arg())
            ->once();

        $this->repository->shouldReceive('find')
            ->withArgs(static fn (TagId $arg): bool => $arg->value === $tagId)
            ->andReturn($this->createTag($tagId, '旧タグ', 10))
            ->once();

        $this->service->shouldReceive('prepareForUpdate')
            ->with($tagId, $name, $orderNo)
            ->andThrow(new BusinessRuleViolationException('検証エラー'))
            ->once();

        $this->expectException(BusinessRuleViolationException::class);

        $result = $this->getInstance()->handle(new UpdateInputData($tagId, $name, $orderNo));
    }

    #[Test]
    public function editFailsWhenTagDoesNotExist(): void
    {
        $tagId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';

        $this->transaction->shouldReceive('scope')
            ->withArgs(static fn (Closure $_) => true)
            ->andReturnUsing(static fn (Closure $arg) => $arg())
            ->once();

        $this->repository->shouldReceive('find')
            ->withArgs(static fn (TagId $arg): bool => $arg->value === $tagId)
            ->andReturnNull()
            ->once();

        $this->expectException(ResourceNotFoundException::class);

        $result = $this->getInstance()->handle(new UpdateInputData($tagId, 'テストタグA', 1));
    }

    private function getInstance(): UpdateUseCase
    {
        $context = $this->privilegedContext();

        return new UpdateUseCase(
            $this->authorizer($context),
            $this->transaction,
            $this->repository,
            $this->service,
            $this->recorder,
        );
    }
}
