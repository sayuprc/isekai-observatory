<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\RegisterStart;

use SensitiveParameter;

readonly class RegisterStartInputData
{
    public function __construct(
        #[SensitiveParameter]
        public string $plainToken,
        public string $email,
        public string $name,
    ) {
    }
}
