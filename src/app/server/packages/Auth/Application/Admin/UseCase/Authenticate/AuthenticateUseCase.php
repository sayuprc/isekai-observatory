<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Authenticate;

use AdminUser\Domain\Models\AdminUserRepositoryInterface;
use Auth\Domain\Models\AuthContext;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\Token\AccessToken\JwtHandlerInterface;
use Support\UseCase\Exceptions\UnauthenticatedException;

readonly class AuthenticateUseCase
{
    public function __construct(
        private JwtHandlerInterface $jwtHandler,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private AdminUserRepositoryInterface $userRepository,
        private AuthContext $context,
    ) {
    }

    /**
     * @throws UnauthenticatedException
     */
    public function handle(AuthenticateInputData $inputData): AuthenticateOutputData
    {
        $payload = $this->jwtHandler->verify($inputData->accessToken);

        if (is_null($payload)) {
            throw new UnauthenticatedException();
        }

        // jti は自前で署名した JWT 由来のため、形式不正は不変条件違反として扱う
        $refreshToken = $this->refreshTokenRepository->findActive(new RefreshTokenId($payload->jti));

        if (is_null($refreshToken)) {
            throw new UnauthenticatedException();
        }

        $user = $this->userRepository->find($refreshToken->adminUserId);

        if (is_null($user)) {
            throw new UnauthenticatedException();
        }

        $this->context->set($user);

        return new AuthenticateOutputData();
    }
}
