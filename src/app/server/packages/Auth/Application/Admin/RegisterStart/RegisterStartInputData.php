<?php

declare(strict_types=1);

namespace Auth\Application\Admin\RegisterStart;

use SensitiveParameter;

readonly class RegisterStartInputData
{
    public function __construct(
        #[SensitiveParameter]
        public string $token,
        public string $email,
        public string $name,
    ) {
    }
}
