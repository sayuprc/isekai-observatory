<?php

declare(strict_types=1);

namespace Release\Domain\Services;

use Release\Domain\Models\Description;
use Release\Domain\Models\ReleaseGroup;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupTitle;
use Release\Domain\Models\ReleaseGroupType;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\Domain\ValueObjects\Note;
use Support\Domain\ValueObjects\OrderNo;
use Tag\Domain\Models\TagReferences;

class ReleaseGroupIntegrityService
{
    public function __construct(private readonly UuidGeneratorInterface $generator)
    {
    }

    /**
     * @param list<array{tagId: string}> $tags
     *
     * @throws BusinessRuleViolationException
     */
    public function prepareForCreate(
        string $title,
        int $typeValue,
        string $description,
        string $note,
        bool $isDisplay,
        int $orderNo,
        array $tags,
    ): ReleaseGroup {
        return $this->build($this->generator->generate(), $title, $typeValue, $description, $note, $isDisplay, $orderNo, $tags);
    }

    /**
     * @param list<array{tagId: string}> $tags
     *
     * @throws BusinessRuleViolationException
     */
    public function prepareForUpdate(
        string $releaseGroupId,
        string $title,
        int $typeValue,
        string $description,
        string $note,
        bool $isDisplay,
        int $orderNo,
        array $tags,
    ): ReleaseGroup {
        return $this->build($releaseGroupId, $title, $typeValue, $description, $note, $isDisplay, $orderNo, $tags);
    }

    /**
     * @param list<array{tagId: string}> $tags
     */
    private function build(
        string $releaseGroupId,
        string $title,
        int $typeValue,
        string $description,
        string $note,
        bool $isDisplay,
        int $orderNo,
        array $tags,
    ): ReleaseGroup {
        return new ReleaseGroup(
            new ReleaseGroupId($releaseGroupId),
            new ReleaseGroupTitle($title),
            $this->toReleaseGroupType($typeValue),
            new Description($description),
            new Note($note),
            $isDisplay,
            new OrderNo($orderNo),
            TagReferences::fromArray($tags),
        );
    }

    /**
     * @throws InvalidDomainException
     */
    private function toReleaseGroupType(int $typeValue): ReleaseGroupType
    {
        return ReleaseGroupType::tryFrom($typeValue) ?? throw new InvalidDomainException("不正なリリースグループ種別です: {$typeValue}");
    }
}
