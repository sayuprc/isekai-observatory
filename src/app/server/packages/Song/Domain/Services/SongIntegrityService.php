<?php

declare(strict_types=1);

namespace Song\Domain\Services;

use Media\Domain\Models\MediaRepositoryInterface;
use Person\Domain\Models\PersonRepositoryInterface;
use Song\Domain\Models\Description;
use Song\Domain\Models\LyricsLink;
use Song\Domain\Models\Media\SongMediaLinks;
use Song\Domain\Models\Persons\SongPersons;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongRepositoryInterface;
use Song\Domain\Models\SongType;
use Song\Domain\Models\Title;
use Support\Contracts\Uuid\UuidGeneratorInterface;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\ValueObjects\Note;
use Support\Domain\ValueObjects\OrderNo;
use Tag\Domain\Models\TagReferences;
use Tag\Domain\Models\TagRepositoryInterface;

/**
 * @phpstan-type _person array{personId: string, role: int, orderNo: int}
 * @phpstan-type _tag array{tagId: string}
 * @phpstan-type _songMedia array{mediaId: string, orderNo: int}
 */
class SongIntegrityService
{
    public function __construct(
        private readonly UuidGeneratorInterface $generator,
        private readonly SongRepositoryInterface $songRepository,
        private readonly PersonRepositoryInterface $personRepository,
        private readonly TagRepositoryInterface $tagRepository,
        private readonly MediaRepositoryInterface $mediaRepository,
    ) {
    }

    /**
     * @param list<_tag>       $tags
     * @param list<_person>    $persons
     * @param list<_songMedia> $media
     *
     * @throws BusinessRuleViolationException
     */
    public function prepareForCreate(
        string $title,
        string $description,
        string $note,
        ?string $lyricsLink,
        int $type,
        bool $isDisplay,
        array $tags,
        array $persons,
        array $media,
    ): Song {
        [$persons, $tags, $media] = $this->buildRelations($persons, $tags, $media);

        $this->assertRelationsExist($persons, $tags, $media);

        return $this->build(
            $this->generator->generate(),
            $title,
            $description,
            $note,
            $lyricsLink,
            $type,
            $isDisplay,
            // 更新時に同じ値になることを防ぐために +10 で採番
            $this->songRepository->getMaxOrderNo() + 10,
            $persons,
            $tags,
            $media,
        );
    }

    /**
     * @param list<_tag>       $tags
     * @param list<_person>    $persons
     * @param list<_songMedia> $media
     *
     * @throws BusinessRuleViolationException
     */
    public function prepareForUpdate(
        string $songId,
        string $title,
        string $description,
        string $note,
        ?string $lyricsLink,
        int $type,
        bool $isDisplay,
        int $orderNo,
        array $tags,
        array $persons,
        array $media,
    ): Song {
        [$persons, $tags, $media] = $this->buildRelations($persons, $tags, $media);

        $this->assertRelationsExist($persons, $tags, $media);

        return $this->build(
            $songId,
            $title,
            $description,
            $note,
            $lyricsLink,
            $type,
            $isDisplay,
            $orderNo,
            $persons,
            $tags,
            $media,
        );
    }

    /**
     * @param list<_person>    $persons
     * @param list<_tag>       $tags
     * @param list<_songMedia> $media
     *
     * @return array{0: SongPersons, 1: TagReferences, 2: SongMediaLinks}
     */
    private function buildRelations(array $persons, array $tags, array $media): array
    {
        return [SongPersons::fromArray($persons), TagReferences::fromArray($tags), SongMediaLinks::fromArray($media)];
    }

    private function assertRelationsExist(SongPersons $persons, TagReferences $tags, SongMediaLinks $media): void
    {
        if (! $this->existsPersons($persons)) {
            throw new BusinessRuleViolationException('指定された人物の一部が存在しません。');
        }

        if (! $this->existsTags($tags)) {
            throw new BusinessRuleViolationException('指定されたタグの一部が存在しません。');
        }

        if (! $this->existsMedia($media)) {
            throw new BusinessRuleViolationException('指定されたメディアの一部が存在しません。');
        }
    }

    private function build(
        string $songId,
        string $title,
        string $description,
        string $note,
        ?string $lyricsLink,
        int $type,
        bool $isDisplay,
        int $orderNo,
        SongPersons $persons,
        TagReferences $tags,
        SongMediaLinks $media,
    ): Song {
        return new Song(
            new SongId($songId),
            new Title($title),
            new Description($description),
            new Note($note),
            $this->toLyricsLink($lyricsLink),
            SongType::fromValue($type),
            $isDisplay,
            new OrderNo($orderNo),
            $tags,
            $persons,
            $media,
        );
    }

    private function toLyricsLink(?string $value): ?LyricsLink
    {
        $normalized = $this->normalizeOptionalString($value);

        return $normalized === null ? null : new LyricsLink($normalized);
    }

    private function existsPersons(SongPersons $persons): bool
    {
        $personIds = [];

        foreach ($persons as $item) {
            if (! isset($personIds[$item->personId->value])) {
                $personIds[$item->personId->value] = $item->personId;
            }
        }

        $founds = $this->personRepository->findByIds(...array_values($personIds));

        return count($personIds) === count($founds);
    }

    private function existsTags(TagReferences $tags): bool
    {
        $tagIds = [];

        foreach ($tags as $tag) {
            if (! isset($tagIds[$tag->tagId->value])) {
                $tagIds[$tag->tagId->value] = $tag->tagId;
            }
        }

        if ($tagIds === []) {
            return true;
        }

        $founds = $this->tagRepository->findByIds(...array_values($tagIds));

        return count($tagIds) === count($founds);
    }

    private function existsMedia(SongMediaLinks $media): bool
    {
        $mediaIds = [];

        foreach ($media as $item) {
            if (! isset($mediaIds[$item->mediaId->value])) {
                $mediaIds[$item->mediaId->value] = $item->mediaId;
            }
        }

        if ($mediaIds === []) {
            return true;
        }

        $founds = $this->mediaRepository->findByIds(...array_values($mediaIds));

        return count($mediaIds) === count($founds);
    }

    private function normalizeOptionalString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
