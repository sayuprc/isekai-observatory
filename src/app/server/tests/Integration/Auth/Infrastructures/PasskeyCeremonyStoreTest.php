<?php

declare(strict_types=1);

namespace Tests\Integration\Auth\Infrastructures;

use Auth\Domain\Models\PasskeyCeremonyState;
use Auth\Domain\Models\PasskeyCeremonyType;
use Auth\Infrastructures\PasskeyCeremonyStore;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PasskeyCeremonyStoreTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        config()->set([
            'auth.passkey.ceremony_cache_store' => 'array',
            'auth.passkey.ceremony_ttl_seconds' => 1,
        ]);

        Cache::store('array')->clear();
    }

    #[Override]
    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function putAndPullConsumesStateOnce(): void
    {
        $state = new PasskeyCeremonyState(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            PasskeyCeremonyType::Register,
            'invitee@example.com',
            '招待ユーザー',
            'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
            '{"challenge":"challenge"}',
        );

        $this->getInstance()->put($state);

        $this->assertEquals($state, $this->getInstance()->pull($state->authCeremonyId));
        $this->assertNull($this->getInstance()->pull($state->authCeremonyId));
    }

    #[Test]
    public function pullReturnsNullAfterTtlExpired(): void
    {
        $state = new PasskeyCeremonyState(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            PasskeyCeremonyType::Login,
            'user@example.com',
            null,
            'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
            '{"challenge":"challenge"}',
        );

        $this->getInstance()->put($state);

        CarbonImmutable::setTestNow(CarbonImmutable::now()->addSeconds(2));

        $this->assertNull($this->getInstance()->pull($state->authCeremonyId));
    }

    #[Test]
    public function serializedStateDoesNotContainInvitationToken(): void
    {
        $state = new PasskeyCeremonyState(
            'AAAAAAAA-AAAA-AAAA-AAAA-AAAAAAAAAAAA',
            PasskeyCeremonyType::Register,
            'invitee@example.com',
            '招待ユーザー',
            'BBBBBBBB-BBBB-BBBB-BBBB-BBBBBBBBBBBB',
            '{"challenge":"challenge"}',
        );

        $payload = $state->toArray();

        $this->assertArrayNotHasKey('token', $payload);
        $this->assertSame('register', $payload['type']);
    }

    private function getInstance(): PasskeyCeremonyStore
    {
        return $this->app->make(PasskeyCeremonyStore::class);
    }
}
