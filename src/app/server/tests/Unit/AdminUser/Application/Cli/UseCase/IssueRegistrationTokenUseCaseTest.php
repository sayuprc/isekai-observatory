<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Application\Cli\UseCase;

use AdminUser\Application\Cli\UseCase\IssueRegistrationToken\IssueRegistrationTokenInputData;
use AdminUser\Application\Cli\UseCase\IssueRegistrationToken\IssueRegistrationTokenUseCase;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\RegistrationToken\ConsumptionStatus;
use AdminUser\Domain\Models\RegistrationToken\ExpiredAt;
use AdminUser\Domain\Models\RegistrationToken\HashedTokenValue;
use AdminUser\Domain\Models\RegistrationToken\RegistrationToken;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenId;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\RegistrationToken\RegistrationTokenIssueService;
use Closure;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class IssueRegistrationTokenUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private AdminUserRepositoryInterface&MockInterface $adminUserRepository;

    private MockInterface&RegistrationTokenIssueService $issueService;

    private MockInterface&RegistrationTokenRepositoryInterface $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->adminUserRepository = Mockery::mock(AdminUserRepositoryInterface::class);
        $this->issueService = Mockery::mock(RegistrationTokenIssueService::class);
        $this->repository = Mockery::mock(RegistrationTokenRepositoryInterface::class);
    }

    #[Test]
    public function canIssue(): void
    {
        $email = 'invitee@example.com';
        $plainToken = 'plain-token';

        $token = new RegistrationToken(
            new RegistrationTokenId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            new HashedTokenValue('hashed'),
            new Email($email),
            Role::General,
            Permissions::reconstruct([]),
            new ExpiredAt(new DateTimeImmutable('+7 days')),
            ConsumptionStatus::Unused,
        );

        $this->transaction->shouldReceive('scope')
            ->withArgs(static fn (Closure $_) => true)
            ->andReturnUsing(static fn (Closure $arg) => $arg())
            ->once();

        $this->adminUserRepository->shouldReceive('findByEmail')
            ->withArgs(static fn (Email $arg): bool => $arg->value === $email)
            ->andReturn(null)
            ->once();

        $this->issueService->shouldReceive('issue')
            ->withArgs(static fn (Email $arg, Role $role, Permissions $permissions): bool => $arg->value === $email
                && $role === Role::General
                && $permissions->toArray() === [])
            ->andReturn(['token' => $token, 'plainToken' => $plainToken])
            ->once();

        $this->repository->shouldReceive('save')
            ->with($token)
            ->andReturn($token)
            ->once();

        $output = $this->getInstance()->handle(
            new IssueRegistrationTokenInputData($email, Role::General->value, []),
        );

        $this->assertSame($plainToken, $output->plainToken);
        $this->assertTrue($output->token->equals($token));
    }

    #[Test]
    public function issueFailsIfEmailAlreadyExists(): void
    {
        $email = 'existing@example.com';

        $this->transaction->shouldReceive('scope')
            ->withArgs(static fn (Closure $_) => true)
            ->andReturnUsing(static fn (Closure $arg) => $arg())
            ->once();

        $this->adminUserRepository->shouldReceive('findByEmail')
            ->withArgs(static fn (Email $arg): bool => $arg->value === $email)
            ->andReturn($this->createAdminUser('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA', $email))
            ->once();

        $this->expectException(BusinessRuleViolationException::class);

        $this->getInstance()->handle(
            new IssueRegistrationTokenInputData($email, Role::General->value, []),
        );
    }

    private function getInstance(): IssueRegistrationTokenUseCase
    {
        return new IssueRegistrationTokenUseCase(
            $this->transaction,
            $this->adminUserRepository,
            $this->issueService,
            $this->repository,
        );
    }
}
