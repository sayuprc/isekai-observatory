<?php

declare(strict_types=1);

namespace Person\Application\Admin\UseCase\List;

use Person\Domain\Models\Person;

readonly class ListOutputData
{
    /**
     * @param array<Person> $persons
     */
    public function __construct(public array $persons)
    {
    }
}
