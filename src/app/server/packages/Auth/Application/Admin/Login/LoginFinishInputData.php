<?php

declare(strict_types=1);

namespace Auth\Application\Admin\Login;

readonly class LoginFinishInputData
{
    /**
     * @param string $credentialJson ブラウザが返した PublicKeyCredential の JSON
     */
    public function __construct(
        public string $authCeremonyId,
        public string $credentialJson,
    ) {
    }
}
