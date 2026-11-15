<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Recovery;

readonly class RecoveryFinishInputData
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
