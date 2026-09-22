<?php

declare(strict_types=1);

namespace Event\Application\Admin\UseCase\Create;

readonly class CreateInputData
{
    /** @param array<string, mixed> $data */
    public function __construct(public array $data)
    {
    }
}
