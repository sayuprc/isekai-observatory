<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Refresh;

use Auth\Domain\Models\Token\RefreshToken\RefreshTokenId;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenRepositoryInterface;
use Auth\Domain\Services\Token\AccessToken\AccessTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\RefreshTokenIssueService;
use Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface;
use Support\Contracts\TransactionInterface;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditLogRecorderInterface;
use Support\UseCase\AuditLog\AuditTargetType;
use Support\UseCase\Exceptions\UnauthenticatedException;

readonly class RefreshUseCase
{
    public function __construct(
        private TransactionInterface $transaction,
        private RefreshTokenRepositoryInterface $refreshTokenRepository,
        private RefreshTokenIssueService $refreshTokenIssueService,
        private AccessTokenIssueService $accessTokenIssueService,
        private TokenHasherInterface $tokenHasher,
        private AuditLogRecorderInterface $recorder,
    ) {
    }

    /**
     * @throws UnauthenticatedException
     */
    public function handle(RefreshInputData $inputData): RefreshOutputData
    {
        return $this->transaction->scope(function () use ($inputData): RefreshOutputData {
            try {
                $refreshTokenId = new RefreshTokenId($inputData->refreshTokenId);
            } catch (InvalidDomainException) {
                // リフレッシュトークン ID は資格情報の一部のため、形式不正も認証失敗として扱う
                throw new UnauthenticatedException();
            }

            $refreshToken = $this->refreshTokenRepository->findActive($refreshTokenId);

            if (is_null($refreshToken)) {
                throw new UnauthenticatedException();
            }

            if (! $this->tokenHasher->verify($inputData->refreshToken, $refreshToken->token->value)) {
                throw new UnauthenticatedException();
            }

            ['token' => $nextRefreshToken, 'plainToken' => $plainToken] = $this->refreshTokenIssueService->issue(
                $refreshToken->adminUserId->value,
            );

            $this->refreshTokenRepository->save($refreshToken->consume());
            $this->refreshTokenRepository->save($nextRefreshToken);

            $this->recorder->record(
                AuditAction::Refresh,
                AuditTargetType::AdminUser,
                $refreshToken->adminUserId,
                [
                    'refresh_token_id' => $nextRefreshToken->refreshTokenId->value,
                ],
                $refreshToken->adminUserId,
            );

            return new RefreshOutputData(
                $this->accessTokenIssueService->issue($nextRefreshToken->refreshTokenId->value),
                $nextRefreshToken->refreshTokenId->value,
                $plainToken,
            );
        });
    }
}
