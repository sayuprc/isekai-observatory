<?php

declare(strict_types=1);

namespace Media\Domain\Services;

use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;
use Media\Domain\Models\Media;
use Media\Domain\Models\MediaId;
use Media\Domain\Models\MediaPublishedAt;
use Media\Domain\Models\MediaRepositoryInterface;
use Media\Domain\Models\MediaTitle;
use Media\Domain\Models\MediaType;
use Media\Domain\Models\MediaUrl;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Exceptions\InvalidDomainException;
use Tag\Domain\Models\TagReferences;

class MediaIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly MediaRepositoryInterface $repository,
    ) {
    }

    /**
     * @param array<int, array{tagId: string}> $tags
     *
     * @throws BusinessRuleViolationException
     */
    public function prepareForCreate(
        string $title,
        string $url,
        string $publishedAt,
        int $typeValue,
        bool $isDisplay,
        array $tags,
    ): Media {
        $media = $this->build(
            $this->generator->generate(),
            $title,
            $url,
            $publishedAt,
            $typeValue,
            $isDisplay,
            $tags,
        );

        if ($this->repository->findByUrl($media->url) !== null) {
            throw new BusinessRuleViolationException('同じURLのメディアが既に存在します');
        }

        return $media;
    }

    /**
     * @param array<int, array{tagId: string}> $tags
     *
     * @throws BusinessRuleViolationException
     */
    public function prepareForUpdate(
        string $mediaId,
        string $title,
        string $url,
        string $publishedAt,
        int $typeValue,
        bool $isDisplay,
        array $tags,
    ): Media {
        $media = $this->build(
            $mediaId,
            $title,
            $url,
            $publishedAt,
            $typeValue,
            $isDisplay,
            $tags,
        );

        $found = $this->repository->findByUrl($media->url);

        if ($found !== null && ! $found->equals($media)) {
            throw new BusinessRuleViolationException('同じURLのメディアが既に存在します');
        }

        return $media;
    }

    /**
     * @param array<int, array{tagId: string}> $tags
     */
    private function build(
        string $mediaId,
        string $title,
        string $url,
        string $publishedAt,
        int $typeValue,
        bool $isDisplay,
        array $tags,
    ): Media {
        return new Media(
            new MediaId($mediaId),
            new MediaTitle($title),
            new MediaUrl($url),
            $this->toPublishedAt($publishedAt),
            $this->toMediaType($typeValue),
            $isDisplay,
            TagReferences::fromArray($tags),
        );
    }

    /**
     * @throws InvalidDomainException
     */
    private function toPublishedAt(string $publishedAt): MediaPublishedAt
    {
        $normalized = trim($publishedAt);

        if ($normalized === '') {
            throw new InvalidDomainException('公開日は必須です');
        }

        try {
            return new MediaPublishedAt(
                new DateTimeImmutable($normalized)->setTimezone(new DateTimeZone(date_default_timezone_get())),
            );
        } catch (DateMalformedStringException) {
            throw new InvalidDomainException('公開日が不正です');
        }
    }

    /**
     * 未知・不正な種別値は投入を止めず MediaType::Other に倒す
     */
    private function toMediaType(int $typeValue): MediaType
    {
        return MediaType::tryFrom($typeValue) ?? MediaType::Other;
    }
}
