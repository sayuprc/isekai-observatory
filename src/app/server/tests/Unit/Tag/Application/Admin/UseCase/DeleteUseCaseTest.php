<?php

declare(strict_types=1);

namespace Tests\Unit\Tag\Application\Admin\UseCase;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\AuthContext;
use Closure;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\Exceptions\PermissionDeniedException;
use Support\UseCase\Exceptions\UnauthenticatedException;
use Tag\Application\Admin\UseCase\Delete\DeleteInputData;
use Tag\Application\Admin\UseCase\Delete\DeleteUseCase;
use Tag\Domain\Models\TagId;
use Tag\Domain\Models\TagRepositoryInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class DeleteUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&TagRepositoryInterface $repository;

    private AuditLogRecorderInterface&MockInterface $recorder;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->transaction->shouldReceive('scope')
            ->withArgs(static fn (Closure $_) => true)
            ->andReturnUsing(static fn (Closure $arg) => $arg())
            ->byDefault();
        $this->repository = Mockery::mock(TagRepositoryInterface::class);
        $this->recorder = Mockery::mock(AuditLogRecorderInterface::class);
        $this->recorder->shouldReceive('record')->byDefault();
    }

    #[Test]
    public function deleteTag(): void
    {
        $this->repository->shouldReceive('find')
            ->withArgs(static fn (TagId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->andReturn($this->createTag('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'タグ', 1))
            ->once();

        $this->repository->shouldReceive('isUsed')
            ->withArgs(static fn (TagId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->andReturn(false)
            ->once();

        $this->repository->shouldReceive('delete')
            ->withArgs(static fn (TagId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->once();

        $result = $this->getInstance()->handle(new DeleteInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));
    }

    #[Test]
    public function cannotDeleteWhenUsed(): void
    {
        $this->repository->shouldReceive('find')
            ->withArgs(static fn (TagId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->andReturn($this->createTag('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', 'タグ', 1))
            ->once();

        $this->repository->shouldReceive('isUsed')
            ->withArgs(static fn (TagId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->andReturn(true)
            ->once();

        $this->repository->shouldNotReceive('delete');

        $this->expectException(BusinessRuleViolationException::class);

        $result = $this->getInstance()->handle(new DeleteInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));
    }

    #[Test]
    public function unauthenticated(): void
    {
        $this->repository->shouldNotReceive('isUsed');
        $this->repository->shouldNotReceive('delete');

        $context = $this->app->make(AuthContext::class);

        $this->expectException(UnauthenticatedException::class);

        $result = $this->getInstance($context)->handle(new DeleteInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));
    }

    #[Test]
    public function unauthorized(): void
    {
        $this->repository->shouldNotReceive('isUsed');
        $this->repository->shouldNotReceive('delete');

        $context = $this->app->make(AuthContext::class);
        $context->set(AdminUser::reconstruct(
            $this->generateUuid(),
            'テストユーザー',
            'test@example.com',
            new DateTimeImmutable(),
            Role::General->value,
            [],
        ));

        $this->expectException(PermissionDeniedException::class);

        $result = $this->getInstance($context)->handle(new DeleteInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));
    }

    #[Test]
    public function invalidTagId(): void
    {
        $this->repository->shouldNotReceive('isUsed');
        $this->repository->shouldNotReceive('delete');

        $this->expectException(InvalidDomainException::class);

        $result = $this->getInstance()->handle(new DeleteInputData('invalid-id'));
    }

    private function getInstance(?AuthContext $context = null): DeleteUseCase
    {
        $context ??= $this->privilegedContext();

        return new DeleteUseCase(
            $this->authorizer($context),
            $this->transaction,
            $this->repository,
            $this->recorder,
        );
    }
}
