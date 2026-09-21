<?php

declare(strict_types=1);

namespace Release\Domain\Services;

use DateMalformedStringException;
use DateType\ImmutableDate;
use Release\Domain\Models\Color;
use Release\Domain\Models\Description;
use Release\Domain\Models\Media;
use Release\Domain\Models\Release;
use Release\Domain\Models\ReleasedOn;
use Release\Domain\Models\ReleaseFormats;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupRepositoryInterface;
use Release\Domain\Models\ReleaseId;
use Release\Domain\Models\ReleaseName;
use Song\Domain\Models\SongRepositoryInterface;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\Domain\ValueObjects\OrderNo;

class ReleaseIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly ReleaseGroupRepositoryInterface $releaseGroupRepository,
        private readonly SongRepositoryInterface $songRepository,
    ) {
    }

    /**
     * @param list<int>                                                                                                     $formatValues
     * @param list<array{position: int, name: ?string, tracks: list<array{songId: ?string, title: ?string, trackNo: int}>}> $media
     *
     * @throws BusinessRuleViolationException
     */
    public function prepareForCreate(
        string $releaseGroupId,
        string $name,
        string $releasedOn,
        string $description,
        string $color,
        bool $isDisplay,
        int $orderNo,
        array $formatValues,
        array $media,
    ): Release {
        return $this->prepare($this->generator->generate(), $releaseGroupId, $name, $releasedOn, $description, $color, $isDisplay, $orderNo, $formatValues, $media);
    }

    /**
     * @param list<int>                                                                                                     $formatValues
     * @param list<array{position: int, name: ?string, tracks: list<array{songId: ?string, title: ?string, trackNo: int}>}> $media
     *
     * @throws BusinessRuleViolationException
     */
    public function prepareForUpdate(
        string $releaseId,
        string $releaseGroupId,
        string $name,
        string $releasedOn,
        string $description,
        string $color,
        bool $isDisplay,
        int $orderNo,
        array $formatValues,
        array $media,
    ): Release {
        return $this->prepare($releaseId, $releaseGroupId, $name, $releasedOn, $description, $color, $isDisplay, $orderNo, $formatValues, $media);
    }

    /**
     * @param list<int>                                                                                                     $formatValues
     * @param list<array{position: int, name: ?string, tracks: list<array{songId: ?string, title: ?string, trackNo: int}>}> $media
     */
    private function prepare(
        string $releaseId,
        string $releaseGroupId,
        string $name,
        string $releasedOn,
        string $description,
        string $color,
        bool $isDisplay,
        int $orderNo,
        array $formatValues,
        array $media,
    ): Release {
        $release = $this->build($releaseId, $releaseGroupId, $name, $releasedOn, $description, $color, $isDisplay, $orderNo, $formatValues, $media);

        if (is_null($this->releaseGroupRepository->find($release->releaseGroupId))) {
            throw new BusinessRuleViolationException('指定されたリリースグループが存在しません。');
        }

        if (! $this->existsSongs($release->media)) {
            throw new BusinessRuleViolationException('指定された楽曲の一部が存在しません。');
        }

        return $release;
    }

    /**
     * @param list<int>                                                                                                     $formatValues
     * @param list<array{position: int, name: ?string, tracks: list<array{songId: ?string, title: ?string, trackNo: int}>}> $media
     */
    private function build(
        string $releaseId,
        string $releaseGroupId,
        string $name,
        string $releasedOn,
        string $description,
        string $color,
        bool $isDisplay,
        int $orderNo,
        array $formatValues,
        array $media,
    ): Release {
        return new Release(
            new ReleaseId($releaseId),
            new ReleaseGroupId($releaseGroupId),
            new ReleaseName($name),
            $this->toReleasedOn($releasedOn),
            new Description($description),
            new Color($color),
            $isDisplay,
            new OrderNo($orderNo),
            ReleaseFormats::fromArray($formatValues),
            Media::fromArray($media),
        );
    }

    private function existsSongs(Media $media): bool
    {
        foreach ($media as $medium) {
            foreach ($medium->tracks as $track) {
                // タイトルのみトラックは Song 集約を参照しない
                if (is_null($track->songId)) {
                    continue;
                }

                if (is_null($this->songRepository->find($track->songId))) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @throws InvalidDomainException
     */
    private function toReleasedOn(string $releasedOn): ReleasedOn
    {
        $normalized = trim($releasedOn);

        if ($normalized === '') {
            throw new InvalidDomainException('発売日は必須です');
        }

        try {
            return new ReleasedOn(new ImmutableDate($normalized));
        } catch (DateMalformedStringException) {
            throw new InvalidDomainException('発売日が不正です');
        }
    }
}
