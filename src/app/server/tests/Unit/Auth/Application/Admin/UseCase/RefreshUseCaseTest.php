<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Application\Admin\UseCase;

use Auth\Application\Admin\UseCase\Refresh\RefreshInputData;
use Auth\Application\Admin\UseCase\Refresh\RefreshUseCase;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface;
use DateTimeImmutable;
use Mockery;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\TransactionInterface;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\Exceptions\UnauthenticatedException;
use Tests\Support\Domain\EntityFactory;
use Tests\TestCase;

class RefreshUseCaseTest extends TestCase
{
    use EntityFactory;

    private MockInterface&TransactionInterface $transaction;

    private RefreshTokenRepositoryInterface $refreshTokenRepository;

    private MockInterface&RefreshTokenIssueService $refreshTokenIssueService;

    private AccessTokenIssueService&MockInterface $accessTokenIssueService;

    private MockInterface&TokenHasherInterface $tokenHasher;

    private AuditLogRecorderInterface&MockInterface $recorder;

    private ?RefreshToken $foundRefreshToken = null;

    /**
     * @var list<RefreshToken>
     */
    private array $savedRefreshTokens = [];

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = Mockery::mock(TransactionInterface::class);
        $this->refreshTokenIssueService = Mockery::mock(RefreshTokenIssueService::class);
        $this->accessTokenIssueService = Mockery::mock(AccessTokenIssueService::class);
        $this->tokenHasher = Mockery::mock(TokenHasherInterface::class);
        $this->recorder = Mockery::mock(AuditLogRecorderInterface::class);
        $this->recorder->shouldReceive('record')->byDefault();

        $this->refreshTokenRepository = new class ($this) implements RefreshTokenRepositoryInterface {
            public function __construct(private readonly RefreshUseCaseTest $test)
            {
            }

            public function findActive(RefreshTokenId $refreshTokenId): ?RefreshToken
            {
                return $this->test->getFoundRefreshToken();
            }

            public function save(RefreshToken $refreshToken): RefreshToken
            {
                $this->test->recordSavedRefreshToken($refreshToken);

                return $refreshToken;
            }
        };

        $this->transaction->shouldReceive('scope')
            ->andReturnUsing(static fn (callable $callback) => $callback());
    }

    #[Test]
    public function canRefresh(): void
    {
        $refreshTokenId = $this->generateUuid();
        $adminUserId = $this->generateUuid();
        $plainToken = 'plain-refresh-token';
        $storedToken = $this->createRefreshToken(
            $refreshTokenId,
            $adminUserId,
            'hashed-token',
            new DateTimeImmutable('2019-12-09 12:00:00'),
            ConsumptionStatus::Unused,
        );
        $nextRefreshToken = $this->createRefreshToken(
            $this->generateUuid(),
            $adminUserId,
            'next-hashed-token',
            new DateTimeImmutable('2019-12-10 12:00:00'),
            ConsumptionStatus::Unused,
        );

        $this->foundRefreshToken = $storedToken;

        $this->refreshTokenIssueService->shouldReceive('issue')
            ->with($adminUserId)
            ->andReturn([
                'token' => $nextRefreshToken,
                'plainToken' => 'next-plain-token',
            ])
            ->once();

        $this->accessTokenIssueService->shouldReceive('issue')
            ->with($nextRefreshToken->refreshTokenId->value)
            ->andReturn($this->createAccessToken('access-token'))
            ->once();

        $this->tokenHasher->shouldReceive('verify')
            ->with($plainToken, $storedToken->token->value)
            ->andReturnTrue()
            ->once();

        $output = $this->getInstance()->handle(new RefreshInputData($storedToken->refreshTokenId->value, $plainToken));

        $this->assertSame('access-token', $output->accessToken->jwt->value);
        $this->assertSame($nextRefreshToken->refreshTokenId->value, $output->refreshTokenId);
        $this->assertSame('next-plain-token', $output->plainRefreshToken);
        $this->assertCount(2, $this->savedRefreshTokens);
        $this->assertTrue($this->savedRefreshTokens[0]->equals($storedToken));
        $this->assertFalse($this->savedRefreshTokens[0]->isAvailable(new DateTimeImmutable('2019-12-09 12:00:00')));
        $this->assertTrue($this->savedRefreshTokens[1]->equals($nextRefreshToken));
    }

    #[Test]
    public function unauthenticatedWhenRefreshTokenIsInvalid(): void
    {
        $this->foundRefreshToken = null;

        $this->refreshTokenIssueService->shouldNotReceive('issue');
        $this->accessTokenIssueService->shouldNotReceive('issue');
        $this->tokenHasher->shouldNotReceive('verify');

        try {
            $this->getInstance()->handle(new RefreshInputData($this->generateUuid(), 'invalid-refresh-token'));
            $this->fail('UnauthenticatedException が発生しませんでした');
        } catch (UnauthenticatedException) {
        }

        $this->assertSame([], $this->savedRefreshTokens);
    }

    #[Test]
    public function unauthenticatedWhenRefreshTokenIdIsMalformed(): void
    {
        $this->refreshTokenIssueService->shouldNotReceive('issue');
        $this->accessTokenIssueService->shouldNotReceive('issue');
        $this->tokenHasher->shouldNotReceive('verify');

        try {
            $this->getInstance()->handle(new RefreshInputData('not-a-uuid', 'plain-refresh-token'));
            $this->fail('UnauthenticatedException が発生しませんでした');
        } catch (UnauthenticatedException) {
        }

        $this->assertSame([], $this->savedRefreshTokens);
    }

    private function getInstance(): RefreshUseCase
    {
        return new RefreshUseCase(
            $this->transaction,
            $this->refreshTokenRepository,
            $this->refreshTokenIssueService,
            $this->accessTokenIssueService,
            $this->tokenHasher,
            $this->recorder,
        );
    }

    public function getFoundRefreshToken(): ?RefreshToken
    {
        return $this->foundRefreshToken;
    }

    public function recordSavedRefreshToken(RefreshToken $refreshToken): void
    {
        $this->savedRefreshTokens[] = $refreshToken;
    }
}
