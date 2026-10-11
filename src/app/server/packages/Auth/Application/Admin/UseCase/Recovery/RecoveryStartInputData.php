<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Recovery;

use SensitiveParameter;

readonly class RecoveryStartInputData
{
    public function __construct(
        public string $email,
        #[SensitiveParameter]
        public string $plainCode,
        public string $name,
    ) {
    }
}
