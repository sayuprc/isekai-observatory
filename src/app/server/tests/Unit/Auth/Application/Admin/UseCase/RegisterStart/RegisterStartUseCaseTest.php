<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Application\Admin\UseCase\RegisterStart;

use AdminUser\Domain\Models\AdminUserName;
use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\RegistrationToken\ConsumptionStatus;
use AdminUser\Domain\Models\RegistrationToken\ExpiredAt;
use AdminUser\Domain\Models\RegistrationToken\HashedTokenValue;
use AdminUser\Domain\Models\RegistrationToken\RegistrationToken;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenId;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\AdminUserIntegrityService;
use AdminUser\Domain\Services\RegistrationToken\RegistrationTokenConsumeService;
use Auth\Application\Admin\UseCase\RegisterStart\RegisterStartInputData;
use Auth\Application\Admin\UseCase\RegisterStart\RegisterStartUseCase;
use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Models\PasskeyCeremonyType;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\PasskeyStartResult;
use Auth\Domain\Services\PasskeyUserHandleGeneratorInterface;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class RegisterStartUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&RegistrationTokenConsumeService $consumeService;

    private AdminUserIntegrityService&MockInterface $integrityService;

    private MockInterface&PasskeyAuthenticatorInterface $passkeyAuthenticator;

    private MockInterface&PasskeyUserHandleGeneratorInterface $userHandleGenerator;

    private MockInterface&PasskeyCeremonyStoreInterface $ceremonyStore;

    private MockInterface&UuidGeneratorInterface $uuidGenerator;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->consumeService = Mockery::mock(RegistrationTokenConsumeService::class);
        $this->integrityService = Mockery::mock(AdminUserIntegrityService::class);
        $this->passkeyAuthenticator = Mockery::mock(PasskeyAuthenticatorInterface::class);
        $this->userHandleGenerator = Mockery::mock(PasskeyUserHandleGeneratorInterface::class);
        $this->ceremonyStore = Mockery::mock(PasskeyCeremonyStoreInterface::class);
        $this->uuidGenerator = Mockery::mock(UuidGeneratorInterface::class);
    }

    #[Test]
    public function canStartRegistration(): void
    {
        $adminUserId = 'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB';
        $authCeremonyId = 'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC';
        $token = $this->buildToken('invitee@example.com');
        $adminUser = $this->createAdminUser($adminUserId, 'invitee@example.com', name: '名前');

        $this->consumeService->shouldReceive('verify')
            ->withArgs(static fn (string $plainToken, Email $email): bool => $plainToken === 'plain-token'
                && $email->value === 'invitee@example.com')
            ->andReturn($token)
            ->once();

        $this->integrityService->shouldReceive('prepareForCreate')
            ->withArgs(static fn (AdminUserName $name, Email $email, Role $role, Permissions $permissions): bool => $name->value === '名前'
                && $email->value === 'invitee@example.com'
                && $role === Role::General
                && $permissions->toArray() === [])
            ->andReturn($adminUser)
            ->once();

        $this->uuidGenerator->shouldReceive('generate')
            ->andReturn($authCeremonyId)
            ->once();

        $this->userHandleGenerator->shouldReceive('generate')
            ->andReturn('fixed-user-handle')
            ->once();

        $this->passkeyAuthenticator->shouldReceive('startRegistration')
            ->withArgs(static fn (string $userHandle, string $userName, string $displayName): bool => $userHandle === 'fixed-user-handle'
                && $userName === 'invitee@example.com'
                && $displayName === '名前')
            ->andReturn(new PasskeyStartResult('{"challenge":"challenge"}', ['challenge' => 'challenge']))
            ->once();

        $this->ceremonyStore->shouldReceive('put')
            ->withArgs(static fn (PasskeyCeremonyState $state): bool => $state->authCeremonyId === $authCeremonyId
                && $state->type === PasskeyCeremonyType::Register
                && $state->email === 'invitee@example.com'
                && $state->name === '名前'
                && $state->adminUserId === $adminUserId
                && $state->optionsJson === '{"challenge":"challenge"}')
            ->once();

        $output = $this->getInstance()->handle(new RegisterStartInputData('plain-token', 'invitee@example.com', '名前'));

        $this->assertSame($authCeremonyId, $output->authCeremonyId);
        $this->assertSame(['challenge' => 'challenge'], $output->publicKey);
    }

    #[Test]
    public function doesNotStoreStateWhenTokenInvalid(): void
    {
        $this->consumeService->shouldReceive('verify')
            ->andReturnNull()
            ->once();
        $this->ceremonyStore->shouldReceive('put')->never();

        $this->expectException(BusinessRuleViolationException::class);

        $this->getInstance()->handle(new RegisterStartInputData('plain-token', 'invitee@example.com', '名前'));
    }

    private function buildToken(string $email): RegistrationToken
    {
        return new RegistrationToken(
            new RegistrationTokenId('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA'),
            new HashedTokenValue('hashed'),
            new Email($email),
            Role::General,
            Permissions::reconstruct([]),
            new ExpiredAt(new DateTimeImmutable('+7 days')),
            ConsumptionStatus::Unused,
        );
    }

    private function getInstance(): RegisterStartUseCase
    {
        return new RegisterStartUseCase(
            $this->consumeService,
            $this->integrityService,
            $this->passkeyAuthenticator,
            $this->userHandleGenerator,
            $this->ceremonyStore,
            $this->uuidGenerator,
        );
    }
}
