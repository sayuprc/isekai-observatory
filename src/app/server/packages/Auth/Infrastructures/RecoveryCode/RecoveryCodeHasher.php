<?php

declare(strict_types=1);

namespace Auth\Infrastructures\RecoveryCode;

use Auth\Domain\Services\RecoveryCode\RecoveryCodeHasherInterface;
use Override;
use SensitiveParameter;

readonly class RecoveryCodeHasher implements RecoveryCodeHasherInterface
{
    public function __construct(#[SensitiveParameter] private string $pepper)
    {
    }

    #[Override]
    public function hash(#[SensitiveParameter] string $plainCode): string
    {
        // リカバリーコードはエントロピーが小さい (約 40bit) ため、ソルト無しの高速ハッシュだと
        // DB 漏洩時にオフライン総当たりが現実的になり得る。DB に載らないアプリ側秘密値 (pepper) を
        // 鍵にした HMAC で総当たりを防ぎつつ、verify は定数時間・高速のまま維持する
        return hash_hmac('sha256', $plainCode, $this->pepper);
    }

    #[Override]
    public function verify(#[SensitiveParameter] string $plainCode, string $hashedCode): bool
    {
        return hash_equals($this->hash($plainCode), $hashedCode);
    }
}
