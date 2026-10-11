<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Infrastructures;

use Auth\Domain\Services\PasskeyConfig;
use Auth\Domain\Services\PasskeyStartResult;
use Auth\Infrastructures\PasskeyAuthenticator;
use Auth\Infrastructures\PasskeyCredentialRecordConverter;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class PasskeyAuthenticatorTest extends TestCase
{
    private function authenticator(): PasskeyAuthenticator
    {
        // config() の上書きを反映するため、設定後に毎回コンテナから解決する
        return new PasskeyAuthenticator(new PasskeyCredentialRecordConverter(), $this->app->make(PasskeyConfig::class));
    }

    #[Test]
    public function startAuthenticationFailsFastWhenOriginHasNoHost(): void
    {
        config()->set('auth.passkey.origin', 'not-a-valid-origin');

        $this->expectException(RuntimeException::class);

        $this->authenticator()->startAuthentication();
    }

    #[Test]
    public function startRegistrationFailsFastWhenOriginHasNoHost(): void
    {
        config()->set('auth.passkey.origin', '');

        $this->expectException(RuntimeException::class);

        $this->authenticator()->startRegistration('user-handle', 'user@example.com', 'ユーザー');
    }

    #[Test]
    public function startAuthenticationSucceedsWithValidOrigin(): void
    {
        config()->set([
            'auth.passkey.origin' => 'https://admin.example.com',
            'auth.passkey.rp_id' => 'admin.example.com',
        ]);

        $result = $this->authenticator()->startAuthentication();

        $this->assertInstanceOf(PasskeyStartResult::class, $result);
    }
}
