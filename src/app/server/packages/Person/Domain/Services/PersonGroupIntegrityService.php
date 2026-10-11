<?php

declare(strict_types=1);

namespace Person\Domain\Services;

use Person\Domain\Models\PersonGroup;
use Person\Domain\Models\PersonGroupId;
use Person\Domain\Models\PersonGroupMembers;
use Person\Domain\Models\PersonGroupName;
use Person\Domain\Models\PersonGroupRepositoryInterface;
use Person\Domain\Models\PersonRepositoryInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;

/**
 * @phpstan-import-type _memberInput from PersonGroupMembers
 */
class PersonGroupIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly PersonGroupRepositoryInterface $repository,
        private readonly PersonRepositoryInterface $personRepository,
    ) {
    }

    /**
     * @param list<_memberInput> $members
     *
     * @throws BusinessRuleViolationException
     */
    public function prepareForCreate(string $name, array $members): PersonGroup
    {
        return $this->build($this->generator->generate(), $name, $members);
    }

    /**
     * @param list<_memberInput> $members
     *
     * @throws BusinessRuleViolationException
     */
    public function prepareForUpdate(string $personGroupId, string $name, array $members): PersonGroup
    {
        return $this->build($personGroupId, $name, $members);
    }

    /**
     * @param list<_memberInput> $members
     *
     * @throws BusinessRuleViolationException
     */
    private function build(string $personGroupId, string $name, array $members): PersonGroup
    {
        $personGroup = new PersonGroup(
            new PersonGroupId($personGroupId),
            new PersonGroupName($name),
            PersonGroupMembers::fromArray($members),
        );

        if (($found = $this->repository->findByName($personGroup->name)) !== null && ! $found->equals($personGroup)) {
            throw new BusinessRuleViolationException(sprintf('すでに使われている名前です "%s"', $name));
        }

        $memberIds = $personGroup->members->personIds();

        if (count($this->personRepository->findByIds(...$memberIds)) !== count($memberIds)) {
            throw new BusinessRuleViolationException('指定された人物の一部が存在しません');
        }

        return $personGroup;
    }
}
