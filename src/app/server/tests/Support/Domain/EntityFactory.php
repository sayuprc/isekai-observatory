<?php

declare(strict_types=1);

namespace Tests\Support\Domain;

use AdminUser\Domain\Models\AdminUser;
use AdminUser\Domain\Models\AdminUserId;
use AdminUser\Domain\Models\Role;
use Auth\Domain\Models\Token\AccessToken\AccessToken;
use Auth\Domain\Models\Token\AccessToken\Jwt;
use Auth\Domain\Models\Token\RefreshToken\ConsumptionStatus;
use Auth\Domain\Models\Token\RefreshToken\ExpiredAt;
use Auth\Domain\Models\Token\RefreshToken\HashedTokenValue;
use Auth\Domain\Models\Token\RefreshToken\RefreshToken;
use Auth\Domain\Models\Token\RefreshToken\RefreshTokenId;
use DateTimeImmutable;
use DateType\ImmutableDate;
use Media\Domain\Models\Media;
use Media\Domain\Models\MediaId;
use Media\Domain\Models\MediaPublishedAt;
use Media\Domain\Models\MediaTitle;
use Media\Domain\Models\MediaType;
use Media\Domain\Models\MediaUrl;
use Media\Domain\Models\YouTubeChannel\YouTubeChannel;
use Person\Domain\Models\Person;
use Person\Domain\Models\PersonId;
use Person\Domain\Models\PersonName;
use Release\Domain\Models\Color;
use Release\Domain\Models\Description as ReleaseDescription;
use Release\Domain\Models\Media as ReleaseMedia;
use Release\Domain\Models\Release;
use Release\Domain\Models\ReleasedOn;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseFormats;
use Release\Domain\Models\ReleaseGroup;
use Release\Domain\Models\ReleaseGroupId;
use Release\Domain\Models\ReleaseGroupTitle;
use Release\Domain\Models\ReleaseGroupType;
use Release\Domain\Models\ReleaseId;
use Release\Domain\Models\ReleaseName;
use Song\Domain\Models\Description;
use Song\Domain\Models\LyricsLink;
use Song\Domain\Models\Media\SongMediaLinks;
use Song\Domain\Models\Persons\SongPersonRole;
use Song\Domain\Models\Persons\SongPersons;
use Song\Domain\Models\Song;
use Song\Domain\Models\SongId;
use Song\Domain\Models\SongType;
use Song\Domain\Models\Tag\SongTag;
use Song\Domain\Models\Tag\SongTagId;
use Song\Domain\Models\Tag\SongTagName;
use Song\Domain\Models\Tags\SongTagReferences;
use Song\Domain\Models\Title;
use Support\Domain\ValueObjects\OrderNo;
use Venue\Domain\Models\Venue;
use Venue\Domain\Models\VenueId;
use Venue\Domain\Models\VenueKind;
use Venue\Domain\Models\VenueName;

trait EntityFactory
{
    protected function createVenue(string $venueId, string $name, VenueKind $kind): Venue
    {
        return new Venue(new VenueId($venueId), new VenueName($name), $kind);
    }

    protected function createPerson(string $personId, string $name, int $orderNo): Person
    {
        return new Person(
            new PersonId($personId),
            new PersonName($name),
            new OrderNo($orderNo),
        );
    }

    protected function createSong(
        string $songId,
        string $title,
        string $description,
        mixed $lyricsLinkOrType,
        mixed $typeOrIsDisplay = null,
        mixed $isDisplayOrOrderNo = true,
        mixed $orderNoOrTags = 1,
        mixed $tagsOrPersons = [],
        mixed $personsOrLyricists = [],
        mixed $lyricistsOrComposers = [],
        mixed $composersOrArrangers = [],
        mixed $arrangers = [],
        mixed $media = [],
    ): Song {
        if ($lyricsLinkOrType instanceof SongType) {
            $lyricsLink = null;
            $type = $lyricsLinkOrType;
            $isDisplay = is_bool($typeOrIsDisplay) ? $typeOrIsDisplay : true;
            $orderNo = is_int($isDisplayOrOrderNo) ? $isDisplayOrOrderNo : 1;
            $tags = is_array($orderNoOrTags) ? $orderNoOrTags : [];
            $persons = $this->normalizeSongPersons(
                is_array($tagsOrPersons) ? $tagsOrPersons : [],
                is_array($personsOrLyricists) ? $personsOrLyricists : [],
                is_array($lyricistsOrComposers) ? $lyricistsOrComposers : [],
                is_array($composersOrArrangers) ? $composersOrArrangers : [],
            );
            $media = $this->normalizeSongMedia(
                is_array($lyricistsOrComposers) ? $lyricistsOrComposers : [],
                is_array($composersOrArrangers) ? $composersOrArrangers : [],
                is_array($arrangers) ? $arrangers : [],
                is_array($media) ? $media : [],
            );
        } else {
            $lyricsLink = is_string($lyricsLinkOrType) ? $lyricsLinkOrType : null;
            $type = $typeOrIsDisplay;
            $isDisplay = is_bool($isDisplayOrOrderNo) ? $isDisplayOrOrderNo : true;
            $orderNo = is_int($orderNoOrTags) ? $orderNoOrTags : 1;
            $tags = is_array($tagsOrPersons) ? $tagsOrPersons : [];
            $persons = $this->normalizeSongPersons(
                is_array($personsOrLyricists) ? $personsOrLyricists : [],
                is_array($lyricistsOrComposers) ? $lyricistsOrComposers : [],
                is_array($composersOrArrangers) ? $composersOrArrangers : [],
                is_array($arrangers) ? $arrangers : [],
            );
            $media = $this->normalizeSongMedia(
                is_array($lyricistsOrComposers) ? $lyricistsOrComposers : [],
                is_array($composersOrArrangers) ? $composersOrArrangers : [],
                is_array($arrangers) ? $arrangers : [],
                is_array($media) ? $media : [],
            );
        }

        assert($type instanceof SongType);

        return new Song(
            new SongId($songId),
            new Title($title),
            new Description($description),
            is_null($lyricsLink) ? null : new LyricsLink($lyricsLink),
            $type,
            $isDisplay,
            new OrderNo($orderNo),
            SongTagReferences::fromArray($tags),
            SongPersons::fromArray($persons),
            SongMediaLinks::fromArray(is_array($media) ? $media : []),
        );
    }

    /**
     * @param list<array<string, mixed>> $personsOrLyricists
     * @param list<array<string, mixed>> $composersOrLegacyLyricists
     * @param list<array<string, mixed>> $arrangersOrLegacyComposers
     * @param list<array<string, mixed>> $legacyArrangers
     *
     * @return list<array{personId: string, role: int, orderNo: int}>
     */
    private function normalizeSongPersons(
        array $personsOrLyricists,
        array $composersOrLegacyLyricists,
        array $arrangersOrLegacyComposers,
        array $legacyArrangers,
    ): array {
        if ($personsOrLyricists === [] || array_key_exists('personId', $personsOrLyricists[0] ?? [])) {
            /** @var list<array{personId: string, role: int, orderNo: int}> */
            return $personsOrLyricists;
        }

        $toPerson = static fn (array $item, SongPersonRole $role): array => [
            'personId' => (string)$item['personId'],
            'role' => $role->value,
            'orderNo' => (int)$item['orderNo'],
        ];

        return [
            ...array_map(static fn (array $item): array => $toPerson($item, SongPersonRole::Lyricist), $personsOrLyricists),
            ...array_map(static fn (array $item): array => $toPerson($item, SongPersonRole::Composer), $composersOrLegacyLyricists),
            ...array_map(static fn (array $item): array => $toPerson($item, SongPersonRole::Arranger), $arrangersOrLegacyComposers),
            ...array_map(static fn (array $item): array => $toPerson($item, SongPersonRole::Arranger), $legacyArrangers),
        ];
    }

    /**
     * @param list<array<string, mixed>> ...$candidates
     *
     * @return list<array{mediaId: string, orderNo: int}>
     */
    private function normalizeSongMedia(array ...$candidates): array
    {
        foreach ($candidates as $candidate) {
            if ($candidate !== [] && array_key_exists('mediaId', $candidate[0] ?? [])) {
                /** @var list<array{mediaId: string, orderNo: int}> */
                return $candidate;
            }
        }

        return [];
    }

    protected function createSongTag(string $songTagId, string $name, int $orderNo): SongTag
    {
        return new SongTag(
            new SongTagId($songTagId),
            new SongTagName($name),
            new OrderNo($orderNo),
        );
    }

    protected function createMedia(
        string $mediaId,
        string $title,
        string $url,
        MediaType $type,
        bool $isDisplay,
        ?DateTimeImmutable $publishedAt = null,
    ): Media {
        return new Media(
            new MediaId($mediaId),
            new MediaTitle($title),
            new MediaUrl($url),
            new MediaPublishedAt($publishedAt ?? new DateTimeImmutable('2024-01-01 00:00:00')),
            $type,
            $isDisplay,
        );
    }

    protected function createYouTubeChannel(string $channelId, string $name): YouTubeChannel
    {
        return YouTubeChannel::reconstruct($channelId, $name);
    }

    protected function createReleaseGroup(
        string $releaseGroupId,
        string $title,
        ReleaseGroupType $type,
        bool $isDisplay = true,
        string $description = 'テスト用リリースグループ',
        int $orderNo = 1,
    ): ReleaseGroup {
        return new ReleaseGroup(
            new ReleaseGroupId($releaseGroupId),
            new ReleaseGroupTitle($title),
            $type,
            new ReleaseDescription($description),
            $isDisplay,
            new OrderNo($orderNo),
        );
    }

    /**
     * @param list<array{position: int, format: int, tracks: list<array{songId: ?string, title: ?string, trackNo: int}>}> $media
     */
    protected function createRelease(
        string $releaseId,
        string $releaseGroupId,
        string $name,
        bool $isDisplay,
        ?ImmutableDate $releasedOn = null,
        string $description = 'テスト用リリース',
        string $color = '#989899',
        ?array $formats = null,
        array $media = [],
        int $orderNo = 1,
    ): Release {
        return new Release(
            new ReleaseId($releaseId),
            new ReleaseGroupId($releaseGroupId),
            new ReleaseName($name),
            new ReleasedOn($releasedOn ?? new ImmutableDate('2024-01-01')),
            new ReleaseDescription($description),
            new Color($color),
            $isDisplay,
            new OrderNo($orderNo),
            ReleaseFormats::reconstruct($formats ?? [ReleaseFormat::Cd->value]),
            ReleaseMedia::reconstruct($media),
        );
    }

    protected function createAdminUser(
        string $adminUserId,
        string $email,
        Role $role = Role::General,
        array $permissions = [],
        ?DateTimeImmutable $createdAt = null,
        string $name = 'テストユーザー',
    ): AdminUser {
        return AdminUser::reconstruct(
            $adminUserId,
            $name,
            $email,
            $createdAt ?? new DateTimeImmutable(),
            $role->value,
            $permissions,
        );
    }

    protected function createAccessToken(string $jwt): AccessToken
    {
        return new AccessToken(new Jwt($jwt));
    }

    protected function createRefreshToken(
        string $refreshTokenId,
        string $adminUserId,
        string $tokenValue,
        DateTimeImmutable $expiredAt,
        ConsumptionStatus $status,
    ): RefreshToken {
        return new RefreshToken(
            new RefreshTokenId($refreshTokenId),
            new AdminUserId($adminUserId),
            new HashedTokenValue($tokenValue),
            new ExpiredAt($expiredAt),
            $status,
        );
    }
}
