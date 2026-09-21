<?php

declare(strict_types=1);

namespace Support\Infrastructures;

use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\MapperBuilder;
use Override;
use Support\Contracts\MapperInterface;

readonly class Mapper implements MapperInterface
{
    public function __construct(private MapperBuilder $builder)
    {
    }

    #[Override]
    public function map(string $signature, mixed $source): mixed
    {
        $json = ! is_string($source) || ! json_validate($source)
            ? (string)json_encode($source)
            : $source;

        return $this->builder
            ->mapper()
            ->map($signature, Source::json($json)->camelCaseKeys());
    }
}
