<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Application\Admin\UseCase;

use Auth\Application\Admin\UseCase\Authenticate\AuthenticateInputData;
use Auth\Application\Admin\UseCase\Authenticate\AuthenticateUseCase;
use Auth\Domain\Models\AuthContext;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Auth\Domain\Services\Token\AccessToken\AccessTokenPayload;
use Auth\Infrastructures\Token\AccessToken\JwtHandler;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;
use Tests\Support\Domain\EntityStore;

class AuthenticateUseCaseTest extends DatabaseTestCase
{
    use EntityFactory;
    use EntityStore;

    #[Test]
    public function canAuthenticate(): void
    {
        Carbon::setTestNow('2019-12-09 10:30:00');

        config()->set([
            'app.url' => 'issuer',
            'auth.jwt.alg' => 'HS256',
            'auth.jwt.key' => str_repeat('k', 256),
        ]);

        $user = $this->createAdminUser($this->generateUuid(), 'example@example.com');
        $refreshToken = $this->createRefreshToken(
            $this->generateUuid(),
            $user->adminUserId->value,
            'token',
            now()->addHour()->toDateTimeImmutable(),
            ConsumptionStatus::Unused,
        );
        $accessToken = $this->createAccessToken(
            $this->createJwt(
                new AccessTokenPayload(
                    'issuer',
                    now()->getTimestamp(),
                    now()->addMinutes(30)->getTimestamp(),
                    now()->getTimestamp(),
                    $refreshToken->refreshTokenId->value,
                ),
            ),
        );

        $this->storeAdminUsers($user);
        $this->storeRefreshTokens($refreshToken);

        $this->getInstance()->handle(new AuthenticateInputData($accessToken->jwt->value));

        $this->assertNotNull($this->app->make(AuthContext::class)->get());
    }

    private function createJwt(AccessTokenPayload $payload): string
    {
        return $this->app->make(JwtHandler::class)->generate($payload);
    }

    private function getInstance(): AuthenticateUseCase
    {
        return $this->app->make(AuthenticateUseCase::class);
    }
}
