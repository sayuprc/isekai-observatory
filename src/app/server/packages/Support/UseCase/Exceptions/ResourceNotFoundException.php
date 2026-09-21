<?php

declare(strict_types=1);

namespace Support\UseCase\Exceptions;

class ResourceNotFoundException extends UseCaseException
{
    public function __construct(
        public readonly string $resourceName,
        public readonly string $identifier,
    ) {
        parent::__construct(sprintf('%sが見つかりません: %s', $resourceName, $identifier));
    }
}
