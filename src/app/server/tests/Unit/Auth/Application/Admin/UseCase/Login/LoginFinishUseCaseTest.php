<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Application\Admin\UseCase\Login;

use Auth\Application\Admin\UseCase\Login\LoginFinishInputData;
use Auth\Application\Admin\UseCase\Login\LoginFinishUseCase;
use Auth\Domain\Models\AdminUserPasskey;
use Auth\Domain\Models\AdminUserPasskeyRepositoryInterface;
use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyStoreInterface;
use Auth\Domain\Models\PasskeyCeremonyType;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\PasskeyAuthenticationResult;
use Auth\Domain\Services\PasskeyAuthenticatorInterface;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Support\Contracts\ClockInterface;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Exceptions\UnauthenticatedException;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class LoginFinishUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private MockInterface&PasskeyCeremonyStoreInterface $ceremonyStore;

    private AdminUserPasskeyRepositoryInterface&MockInterface $passkeyRepository;

    private MockInterface&PasskeyAuthenticatorInterface $passkeyAuthenticator;

    private MockInterface&RefreshTokenIssueService $refreshTokenIssueService;

    private AccessTokenIssueService&MockInterface $accessTokenIssueService;

    private MockInterface&RefreshTokenRepositoryInterface $refreshTokenRepository;

    private AuditLogRecorderInterface&MockInterface $recorder;

    private ClockInterface&MockInterface $clock;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->ceremonyStore = Mockery::mock(PasskeyCeremonyStoreInterface::class);
        $this->passkeyRepository = Mockery::mock(AdminUserPasskeyRepositoryInterface::class);
        $this->passkeyAuthenticator = Mockery::mock(PasskeyAuthenticatorInterface::class);
        $this->refreshTokenIssueService = Mockery::mock(RefreshTokenIssueService::class);
        $this->accessTokenIssueService = Mockery::mock(AccessTokenIssueService::class);
        $this->refreshTokenRepository = Mockery::mock(RefreshTokenRepositoryInterface::class);
        $this->recorder = Mockery::mock(AuditLogRecorderInterface::class);
        $this->clock = Mockery::mock(ClockInterface::class);

        $this->transaction->shouldReceive('scope')
            ->andReturnUsing(static fn (callable $callback) => $callback())
            ->byDefault();
    }

    #[Test]
    public function canFinishWithLockedPasskeyAndCounterCas(): void
    {
        $adminUserId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $state = $this->loginState($adminUserId);
        $passkey = $this->passkey($adminUserId);
        $refreshToken = $this->refreshToken($adminUserId);
        $now = new DateTimeImmutable('2026-01-02 03:04:05');

        $this->ceremonyStore->shouldReceive('pull')
            ->with($state->authCeremonyId)
            ->andReturn($state)
            ->once();
        $this->passkeyAuthenticator->shouldReceive('credentialId')
            ->with(['id' => 'credential-id'])
            ->andReturn('credential-id')
            ->once();
        $this->passkeyRepository->shouldReceive('findByAdminUserIdAndCredentialIdForUpdate')
            ->with($adminUserId, 'credential-id')
            ->andReturn($passkey)
            ->once();
        $this->passkeyAuthenticator->shouldReceive('finishAuthentication')
            ->with(['id' => 'credential-id'], $state->optionsJson, $passkey, $passkey->userHandle)
            ->andReturn(new PasskeyAuthenticationResult('credential-id', 456))
            ->once();
        $this->refreshTokenIssueService->shouldReceive('issue')
            ->with($adminUserId)
            ->andReturn(['token' => $refreshToken, 'plainToken' => 'plain-refresh-token'])
            ->once();
        $this->clock->shouldReceive('now')
            ->andReturn($now)
            ->once();
        $this->passkeyRepository->shouldReceive('updateCounter')
            ->withArgs(static fn (AdminUserPasskey $updated, int $expectedSignCount): bool => $updated->adminUserPasskeyId === $passkey->adminUserPasskeyId
                && $updated->signCount === 456
                && $updated->lastUsedAt === $now
                && $expectedSignCount === 123)
            ->andReturnTrue()
            ->once();
        $this->accessTokenIssueService->shouldReceive('issue')
            ->with($refreshToken->refreshTokenId->value)
            ->andReturn($this->createAccessToken('access-token'))
            ->once();
        $this->refreshTokenRepository->shouldReceive('save')
            ->with($refreshToken)
            ->andReturn($refreshToken)
            ->once();
        $this->recorder->shouldReceive('record')
            ->withArgs(static fn (AuditAction $action, AuditTargetType $targetType): bool => $action === AuditAction::Login
                && $targetType === AuditTargetType::AdminUser)
            ->once();

        $output = $this->getInstance()->handle(new LoginFinishInputData($state->authCeremonyId, ['id' => 'credential-id']));

        $this->assertSame('access-token', $output->accessToken->jwt->value);
        $this->assertSame($refreshToken->refreshTokenId->value, $output->refreshTokenId);
        $this->assertSame('plain-refresh-token', $output->plainRefreshToken);
    }

    #[Test]
    public function unauthenticatedWhenCredentialIdIsInvalid(): void
    {
        $state = $this->loginState('AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA');

        $this->ceremonyStore->shouldReceive('pull')->andReturn($state)->once();
        $this->passkeyAuthenticator->shouldReceive('credentialId')->andReturnNull()->once();
        $this->transaction->shouldReceive('scope')->never();

        $this->expectException(UnauthenticatedException::class);

        $this->getInstance()->handle(new LoginFinishInputData($state->authCeremonyId, []));
    }

    #[Test]
    public function unauthenticatedWhenLockedPasskeyIsNotFound(): void
    {
        $adminUserId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $state = $this->loginState($adminUserId);

        $this->ceremonyStore->shouldReceive('pull')->andReturn($state)->once();
        $this->passkeyAuthenticator->shouldReceive('credentialId')->andReturn('credential-id')->once();
        $this->passkeyRepository->shouldReceive('findByAdminUserIdAndCredentialIdForUpdate')
            ->with($adminUserId, 'credential-id')
            ->andReturnNull()
            ->once();
        $this->passkeyAuthenticator->shouldReceive('finishAuthentication')->never();
        $this->refreshTokenIssueService->shouldReceive('issue')->never();

        $this->expectException(UnauthenticatedException::class);

        $this->getInstance()->handle(new LoginFinishInputData($state->authCeremonyId, ['id' => 'credential-id']));
    }

    #[Test]
    public function unauthenticatedWhenVerificationFails(): void
    {
        $adminUserId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $state = $this->loginState($adminUserId);
        $passkey = $this->passkey($adminUserId);

        $this->ceremonyStore->shouldReceive('pull')->andReturn($state)->once();
        $this->passkeyAuthenticator->shouldReceive('credentialId')->andReturn('credential-id')->once();
        $this->passkeyRepository->shouldReceive('findByAdminUserIdAndCredentialIdForUpdate')
            ->andReturn($passkey)
            ->once();
        $this->passkeyAuthenticator->shouldReceive('finishAuthentication')
            ->andThrow(new RuntimeException('verification failed'))
            ->once();
        $this->passkeyRepository->shouldReceive('updateCounter')->never();
        $this->refreshTokenIssueService->shouldReceive('issue')->never();

        $this->expectException(UnauthenticatedException::class);

        $this->getInstance()->handle(new LoginFinishInputData($state->authCeremonyId, ['id' => 'credential-id']));
    }

    #[Test]
    public function unauthenticatedWhenCounterUpdateFails(): void
    {
        $adminUserId = 'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA';
        $state = $this->loginState($adminUserId);
        $passkey = $this->passkey($adminUserId);
        $refreshToken = $this->refreshToken($adminUserId);

        $this->ceremonyStore->shouldReceive('pull')->andReturn($state)->once();
        $this->passkeyAuthenticator->shouldReceive('credentialId')->andReturn('credential-id')->once();
        $this->passkeyRepository->shouldReceive('findByAdminUserIdAndCredentialIdForUpdate')
            ->andReturn($passkey)
            ->once();
        $this->passkeyAuthenticator->shouldReceive('finishAuthentication')
            ->andReturn(new PasskeyAuthenticationResult('credential-id', 456))
            ->once();
        $this->refreshTokenIssueService->shouldReceive('issue')
            ->andReturn(['token' => $refreshToken, 'plainToken' => 'plain-refresh-token'])
            ->once();
        $this->clock->shouldReceive('now')
            ->andReturn(new DateTimeImmutable('2026-01-02 03:04:05'))
            ->once();
        $this->passkeyRepository->shouldReceive('updateCounter')
            ->andReturnFalse()
            ->once();
        $this->refreshTokenRepository->shouldReceive('save')->never();
        $this->recorder->shouldReceive('record')->never();

        $this->expectException(UnauthenticatedException::class);

        $this->getInstance()->handle(new LoginFinishInputData($state->authCeremonyId, ['id' => 'credential-id']));
    }

    private function getInstance(): LoginFinishUseCase
    {
        return new LoginFinishUseCase(
            $this->transaction,
            $this->ceremonyStore,
            $this->passkeyRepository,
            $this->passkeyAuthenticator,
            $this->refreshTokenIssueService,
            $this->accessTokenIssueService,
            $this->refreshTokenRepository,
            $this->recorder,
            $this->clock,
        );
    }

    private function loginState(string $adminUserId): PasskeyCeremonyState
    {
        return new PasskeyCeremonyState(
            'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
            PasskeyCeremonyType::Login,
            'user@example.com',
            null,
            $adminUserId,
            '{"challenge":"login-challenge"}',
        );
    }

    private function passkey(string $adminUserId): AdminUserPasskey
    {
        return new AdminUserPasskey(
            'CCCCCCCC-CCCC-CCCC-CCCC-CCCCCCCCCCCC',
            $adminUserId,
            'user-handle',
            'Primary passkey',
            'credential-id',
            'public-key',
            '00000000-0000-0000-0000-000000000000',
            ['internal'],
            true,
            false,
            123,
            new DateTimeImmutable('2026-01-01 00:00:00'),
            null,
        );
    }

    private function refreshToken(string $adminUserId): RefreshToken
    {
        return $this->createRefreshToken(
            'DDDDDDDD-DDDD-DDDD-DDDD-DDDDDDDDDDDD',
            $adminUserId,
            'hashed-refresh-token',
            new DateTimeImmutable('2026-01-09 00:00:00'),
            ConsumptionStatus::Unused,
        );
    }
}
