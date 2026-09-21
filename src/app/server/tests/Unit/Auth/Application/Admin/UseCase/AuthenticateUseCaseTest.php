<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Application\Admin\UseCase;

use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use AdminUser\Domain\Models\Role;
use Auth\Application\Admin\UseCase\Authenticate\AuthenticateInputData;
use Auth\Application\Admin\UseCase\Authenticate\AuthenticateUseCase;
use Auth\Domain\Models\AuthContext;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\Token\AccessToken\AccessTokenPayload;
use Auth\Domain\Services\Token\AccessToken\JwtHandlerInterface;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\UseCase\Exceptions\UnauthenticatedException;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class AuthenticateUseCaseTest extends TestCase
{
    use EntityFactory;

    private JwtHandlerInterface&MockInterface $jwtHandler;

    private MockInterface&RefreshTokenRepositoryInterface $refreshTokenRepository;

    private AdminUserRepositoryInterface&MockInterface $userRepository;

    private AuthContext $context;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->jwtHandler = Mockery::mock(JwtHandlerInterface::class);
        $this->refreshTokenRepository = Mockery::mock(RefreshTokenRepositoryInterface::class);
        $this->userRepository = Mockery::mock(AdminUserRepositoryInterface::class);
        $this->context = new AuthContext();
    }

    #[Test]
    public function canAuthenticate(): void
    {
        $refreshTokenId = $this->generateUuid();

        $this->jwtHandler->shouldReceive('verify')
            ->with('access_token')
            ->andReturn(new AccessTokenPayload('', 0, 0, 0, $refreshTokenId))
            ->once();

        $adminUserId = $this->generateUuid();

        $this->refreshTokenRepository->shouldReceive('findActive')
            ->withArgs(static fn (RefreshTokenId $arg) => $arg->value === $refreshTokenId)
            ->andReturn(
                $this->createRefreshToken(
                    $refreshTokenId,
                    $adminUserId,
                    'token',
                    new DateTimeImmutable(),
                    ConsumptionStatus::Unused,
                ),
            )
            ->once();

        $this->userRepository->shouldReceive('find')
            ->withArgs(static fn (AdminUserId $arg) => $arg->value === $adminUserId)
            ->andReturn($this->createAdminUser($adminUserId, 'example@example.com', Role::General, []))
            ->once();

        $this->getInstance()->handle(new AuthenticateInputData('access_token'));

        $this->assertNotNull($this->context->get());
    }

    #[Test]
    public function unauthenticatedWhenExpireToken(): void
    {
        $this->jwtHandler->shouldReceive('verify')
            ->with('access_token')
            ->andReturnNull()
            ->once();

        $this->expectException(UnauthenticatedException::class);

        $this->getInstance()->handle(new AuthenticateInputData('access_token'));
    }

    #[Test]
    public function unauthenticatedWhenCredentialNotFound(): void
    {
        $refreshTokenId = $this->generateUuid();

        $this->jwtHandler->shouldReceive('verify')
            ->with('access_token')
            ->andReturn(new AccessTokenPayload('', 0, 0, 0, $refreshTokenId))
            ->once();

        $this->refreshTokenRepository->shouldReceive('findActive')
            ->withArgs(static fn (RefreshTokenId $arg) => $arg->value === $refreshTokenId)
            ->andReturnNull()
            ->once();

        $this->expectException(UnauthenticatedException::class);

        $this->getInstance()->handle(new AuthenticateInputData('access_token'));
    }

    #[Test]
    public function unauthenticatedWhenUserNotFound(): void
    {
        $refreshTokenId = $this->generateUuid();

        $this->jwtHandler->shouldReceive('verify')
            ->with('access_token')
            ->andReturn(new AccessTokenPayload('', 0, 0, 0, $refreshTokenId))
            ->once();

        $adminUserId = $this->generateUuid();

        $this->refreshTokenRepository->shouldReceive('findActive')
            ->withArgs(static fn (RefreshTokenId $arg) => $arg->value === $refreshTokenId)
            ->andReturn(
                $this->createRefreshToken(
                    $refreshTokenId,
                    $adminUserId,
                    'token',
                    new DateTimeImmutable(),
                    ConsumptionStatus::Unused,
                ),
            )
            ->once();

        $this->userRepository->shouldReceive('find')
            ->withArgs(static fn (AdminUserId $arg) => $arg->value === $adminUserId)
            ->andReturnNull()
            ->once();

        $this->expectException(UnauthenticatedException::class);

        $this->getInstance()->handle(new AuthenticateInputData('access_token'));
    }

    private function getInstance(): AuthenticateUseCase
    {
        return new AuthenticateUseCase(
            $this->jwtHandler,
            $this->refreshTokenRepository,
            $this->userRepository,
            $this->context,
        );
    }
}
