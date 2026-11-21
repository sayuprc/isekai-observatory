<?php

declare(strict_types=1);

namespace Auth\Application\Admin\RegisterFinish;

use SensitiveParameter;

readonly class RegisterFinishInputData
{
    /**
     * @param string $credentialJson ブラウザが返した PublicKeyCredential の JSON
     */
    public function __construct(
        public string $authCeremonyId,
        #[SensitiveParameter]
        public string $token,
        public string $credentialJson,
    ) {
    }
}
