<?php

declare(strict_types=1);

namespace Tests\Unit\AdminUser\Infrastructures\RegistrationToken;

use AdminUser\Infrastructures\RegistrationToken\TokenHasher;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TokenHasherTest extends TestCase
{
    #[Test]
    public function hashesWithSha256(): void
    {
        $plainToken = str_repeat('a', 128);

        $hashed = $this->getInstance()->hash($plainToken);

        $this->assertSame(hash('sha256', $plainToken), $hashed);
        $this->assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $hashed);
    }

    #[Test]
    public function verifiesSha256Hash(): void
    {
        $plainToken = str_repeat('a', 128);
        $hashed = hash('sha256', $plainToken);

        $this->assertTrue($this->getInstance()->verify($plainToken, $hashed));
        $this->assertFalse($this->getInstance()->verify(str_repeat('b', 128), $hashed));
    }

    private function getInstance(): TokenHasher
    {
        return new TokenHasher();
    }
}
