<?php

declare(strict_types=1);

namespace Person\Domain\Models;

interface PersonGroupRepositoryInterface
{
    public function find(PersonGroupId $personGroupId): ?PersonGroup;

    /**
     * @return list<PersonGroup>
     */
    public function findByIds(PersonGroupId ...$personGroupIds): array;

    public function findByName(PersonGroupName $name): ?PersonGroup;

    public function save(PersonGroup $personGroup): PersonGroup;

    public function delete(PersonGroupId $personGroupId): void;
}
