<?php

declare(strict_types=1);

namespace Tests\Unit\Person\Application\Admin\UseCase;

use Closure;
use Mockery;
use Mockery\MockInterface;
use Override;
use Person\Application\Admin\UseCase\Delete\DeleteInputData;
use Person\Application\Admin\UseCase\Delete\DeleteUseCase;
use Person\Domain\Models\PersonId;
use Person\Domain\Models\PersonRepositoryInterface;
use Person\Domain\Services\PersonUsageCheckerInterface;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class DeleteUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&PersonRepositoryInterface $repository;

    private MockInterface&PersonUsageCheckerInterface $usageChecker;

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
        $this->repository = Mockery::mock(PersonRepositoryInterface::class);
        $this->usageChecker = Mockery::mock(PersonUsageCheckerInterface::class);
        $this->recorder = Mockery::mock(AuditLogRecorderInterface::class);
        $this->recorder->shouldReceive('record')->byDefault();
    }

    #[Test]
    public function deletePerson(): void
    {
        $this->repository->shouldReceive('find')
            ->withArgs(static fn (PersonId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->andReturn($this->createPerson('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', '人物', 1))
            ->once();

        $this->usageChecker->shouldReceive('isUsed')
            ->withArgs(static fn (PersonId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->andReturn(false)
            ->once();

        $this->repository->shouldReceive('delete')
            ->withArgs(static fn (PersonId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->once();

        $result = $this->getInstance()->handle(new DeleteInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));
    }

    #[Test]
    public function cannotDeleteWhenUsedInSong(): void
    {
        $this->repository->shouldReceive('find')
            ->withArgs(static fn (PersonId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->andReturn($this->createPerson('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', '人物', 1))
            ->once();

        $this->usageChecker->shouldReceive('isUsed')
            ->withArgs(static fn (PersonId $arg): bool => $arg->value === 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA')
            ->andReturn(true)
            ->once();

        $this->repository->shouldNotReceive('delete');

        $this->expectException(BusinessRuleViolationException::class);

        $result = $this->getInstance()->handle(new DeleteInputData('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'));
    }

    #[Test]
    public function invalidPersonId(): void
    {
        $this->usageChecker->shouldNotReceive('isUsed');
        $this->repository->shouldNotReceive('delete');

        $this->expectException(InvalidDomainException::class);

        $result = $this->getInstance()->handle(new DeleteInputData('invalid-id'));
    }

    private function getInstance(): DeleteUseCase
    {
        $context = $this->privilegedContext();

        return new DeleteUseCase(
            $this->authorizer($context),
            $this->transaction,
            $this->repository,
            $this->usageChecker,
            $this->recorder,
            $context,
        );
    }
}
