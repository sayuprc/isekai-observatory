<?php

declare(strict_types=1);

namespace Tests\Integration\AdminUser\Domain\Services\RegistrationToken;

use AdminUser\Domain\Models\Email;
use AdminUser\Domain\Models\Permissions;
use AdminUser\Domain\Models\RegistrationToken\ConsumptionStatus;
use AdminUser\Domain\Models\RegistrationToken\ExpiredAt;
use AdminUser\Domain\Models\RegistrationToken\HashedTokenValue;
use AdminUser\Domain\Models\RegistrationToken\RegistrationToken;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenId;
use AdminUser\Domain\Models\RegistrationToken\RegistrationTokenRepositoryInterface;
use AdminUser\Domain\Models\Role;
use AdminUser\Domain\Services\RegistrationToken\RegistrationTokenConsumeService;
use AdminUser\Domain\Services\RegistrationToken\TokenHasherInterface;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\Uuid\UuidConverterInterface;
use Tests\Support\DatabaseTestCase;

class RegistrationTokenConsumeServiceTest extends DatabaseTestCase
{
    #[Test]
    public function canVerifyOlderUnusedTokenEvenWhenNewerUnusedTokenExists(): void
    {
        $hasher = $this->app->make(TokenHasherInterface::class);
        $older = $this->saveToken('plain-old-token', 'invitee@example.com', new DateTimeImmutable('-1 hour'));
        $newer = $this->saveToken('plain-new-token', 'invitee@example.com', new DateTimeImmutable());

        $result = $this->getInstance()->verify('plain-old-token', new Email('invitee@example.com'));

        $this->assertNotNull($result);
        $this->assertTrue($result->equals($older));
        $this->assertFalse($result->equals($newer));
        $this->assertTrue($hasher->verify('plain-old-token', $result->token->value));
    }

    private function saveToken(string $plainToken, string $email, DateTimeImmutable $createdAt): RegistrationToken
    {
        $token = new RegistrationToken(
            new RegistrationTokenId($this->generateUuid()),
            new HashedTokenValue($this->app->make(TokenHasherInterface::class)->hash($plainToken)),
            new Email($email),
            Role::General,
            Permissions::reconstruct([]),
            new ExpiredAt(new DateTimeImmutable('+7 days')),
            ConsumptionStatus::Unused,
        );

        $this->app->make(RegistrationTokenRepositoryInterface::class)->save($token);

        DB::table('admin_user_registration_tokens')
            ->where(
                'admin_user_registration_token_id',
                $this->app->make(UuidConverterInterface::class)->toBin($token->registrationTokenId->value),
            )
            ->update([
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
                'updated_at' => $createdAt->format('Y-m-d H:i:s'),
            ]);

        return $token;
    }

    private function getInstance(): RegistrationTokenConsumeService
    {
        return $this->app->make(RegistrationTokenConsumeService::class);
    }
}
