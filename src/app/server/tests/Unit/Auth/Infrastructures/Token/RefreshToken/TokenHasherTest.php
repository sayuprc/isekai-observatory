<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Infrastructures\Token\RefreshToken;

use Auth\Infrastructures\Token\RefreshToken\TokenHasher;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TokenHasherTest extends TestCase
{
    #[Test]
    public function hashCreatesHashedToken(): void
    {
        $hasher = $this->getInstance();
        $plainToken = 'my-plain-token-value-12345';

        $hashedToken = $hasher->hash($plainToken);

        $this->assertNotEquals($plainToken, $hashedToken);
        $this->assertStringStartsWith('$2y$', $hashedToken);
    }

    #[Test]
    public function verifyReturnsTrueForMatchingToken(): void
    {
        $hasher = $this->getInstance();
        $plainToken = 'my-plain-token-value-12345';
        $hashedToken = $hasher->hash($plainToken);

        $result = $hasher->verify($plainToken, $hashedToken);

        $this->assertTrue($result);
    }

    #[Test]
    public function verifyReturnsFalseForNonMatchingToken(): void
    {
        $hasher = $this->getInstance();
        $plainToken = 'my-plain-token-value-12345';
        $wrongToken = 'different-token-value';
        $hashedToken = $hasher->hash($plainToken);

        $result = $hasher->verify($wrongToken, $hashedToken);

        $this->assertFalse($result);
    }

    #[Test]
    public function hashCreatesUniqueHashesForSameInput(): void
    {
        $hasher = $this->getInstance();
        $plainToken = 'my-plain-token-value-12345';

        $hash1 = $hasher->hash($plainToken);
        $hash2 = $hasher->hash($plainToken);

        $this->assertNotEquals($hash1, $hash2);
        $this->assertTrue($hasher->verify($plainToken, $hash1));
        $this->assertTrue($hasher->verify($plainToken, $hash2));
    }

    private function getInstance(): TokenHasher
    {
        return new TokenHasher();
    }
}
