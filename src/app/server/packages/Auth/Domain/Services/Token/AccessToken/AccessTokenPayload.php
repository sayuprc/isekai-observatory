<?php

declare(strict_types=1);

namespace Auth\Domain\Services\Token\AccessToken;

readonly class AccessTokenPayload
{
    public function __construct(
        public string $iss,
        public int $iat,
        public int $exp,
        public int $nbf,
        public string $jti,
    ) {
    }

    /**
     * @return array{
     *  iss: string,
     *  iat: int,
     *  exp: int,
     *  nbf: int,
     *  jti: string,
     * }
     */
    public function toArray(): array
    {
        return [
            'iss' => $this->iss,
            'iat' => $this->iat,
            'exp' => $this->exp,
            'nbf' => $this->nbf,
            'jti' => $this->jti,
        ];
    }
}
