<?php

declare(strict_types=1);

namespace Release\Domain\Services;

use Release\Domain\Models\Description;
use Release\Domain\Models\ReleaseGroup;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupTitle;
use Release\Domain\Models\ReleaseGroupType;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\Domain\ValueObjects\OrderNo;

class ReleaseGroupIntegrityService
{
    public function __construct(private readonly UuidGeneratorInterface $generator)
    {
    }

    public function prepareForCreate(
        string $title,
        int $typeValue,
        string $description,
        bool $isDisplay,
        int $orderNo,
    ): ReleaseGroup {
        return $this->build($this->generator->generate(), $title, $typeValue, $description, $isDisplay, $orderNo);
    }

    public function prepareForUpdate(
        string $releaseGroupId,
        string $title,
        int $typeValue,
        string $description,
        bool $isDisplay,
        int $orderNo,
    ): ReleaseGroup {
        return $this->build($releaseGroupId, $title, $typeValue, $description, $isDisplay, $orderNo);
    }

    private function build(
        string $releaseGroupId,
        string $title,
        int $typeValue,
        string $description,
        bool $isDisplay,
        int $orderNo,
    ): ReleaseGroup {
        return new ReleaseGroup(
            new ReleaseGroupId($releaseGroupId),
            new ReleaseGroupTitle($title),
            $this->toReleaseGroupType($typeValue),
            new Description($description),
            $isDisplay,
            new OrderNo($orderNo),
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
