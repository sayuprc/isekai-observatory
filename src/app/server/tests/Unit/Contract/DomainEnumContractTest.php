<?php

declare(strict_types=1);

namespace Tests\Unit\Contract;

use AdminUser\Domain\Models\Permission;
use AdminUser\Domain\Models\Role;
use BackedEnum;
use Event\Domain\Models\EventStatus;
use Event\Domain\Models\EventType;
use Media\Domain\Models\MediaType;
use OpenAPI\Admin\Client\Model\AuditAction as ContractAuditAction;
use OpenAPI\Admin\Client\Model\AuditTargetType as ContractAuditTargetType;
use OpenAPI\Admin\Client\Model\EventStatusValue;
use OpenAPI\Admin\Client\Model\EventTypeValue;
use OpenAPI\Admin\Client\Model\MediaTypeValue;
use OpenAPI\Admin\Client\Model\PermissionValue;
use OpenAPI\Admin\Client\Model\ReleaseFormatValue;
use OpenAPI\Admin\Client\Model\ReleaseGroupTypeValue;
use OpenAPI\Admin\Client\Model\RoleValue;
use OpenAPI\Admin\Client\Model\SongPersonRole as ContractSongPersonRole;
use OpenAPI\Admin\Client\Model\SongTypeValue;
use OpenAPI\Admin\Client\Model\VenueKindValue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Release\Domain\Models\ReleaseFormat;
use Release\Domain\Models\ReleaseGroupType;
use Song\Domain\Models\Persons\SongPersonRole;
use Song\Domain\Models\SongType;
use Support\UseCase\AuditLog\AuditAction;
use Support\UseCase\AuditLog\AuditTargetType;
use Tests\TestCase;
use Venue\Domain\Models\VenueKind;

class DomainEnumContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string<BackedEnum>, class-string<BackedEnum>}>
     */
    public static function provideEnums(): iterable
    {
        yield 'Role' => [Role::class, RoleValue::class];
        yield 'Permission' => [Permission::class, PermissionValue::class];
        yield 'SongType' => [SongType::class, SongTypeValue::class];
        yield 'SongPersonRole' => [SongPersonRole::class, ContractSongPersonRole::class];
        yield 'MediaType' => [MediaType::class, MediaTypeValue::class];
        yield 'EventType' => [EventType::class, EventTypeValue::class];
        yield 'EventStatus' => [EventStatus::class, EventStatusValue::class];
        yield 'VenueKind' => [VenueKind::class, VenueKindValue::class];
        yield 'ReleaseGroupType' => [ReleaseGroupType::class, ReleaseGroupTypeValue::class];
        yield 'ReleaseFormat' => [ReleaseFormat::class, ReleaseFormatValue::class];
        yield 'AuditAction' => [AuditAction::class, ContractAuditAction::class];
        yield 'AuditTargetType' => [AuditTargetType::class, ContractAuditTargetType::class];
    }

    /**
     * @param class-string<BackedEnum> $domain
     * @param class-string<BackedEnum> $contract
     */
    #[Test]
    #[DataProvider('provideEnums')]
    public function domainEnumHasSameValuesAsContract(string $domain, string $contract): void
    {
        $this->assertEqualsCanonicalizing($this->valuesOf($contract), $this->valuesOf($domain));
    }

    /**
     * @param class-string<BackedEnum> $enum
     *
     * @return list<int|string>
     */
    private function valuesOf(string $enum): array
    {
        return array_map(static fn (BackedEnum $case): int|string => $case->value, $enum::cases());
    }
}
