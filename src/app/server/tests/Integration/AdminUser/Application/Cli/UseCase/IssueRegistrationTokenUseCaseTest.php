<?php

declare(strict_types=1);

namespace Tests\Integration\AdminUser\Application\Cli\UseCase;

use AdminUser\Application\Cli\UseCase\IssueRegistrationToken\IssueRegistrationTokenInputData;
use AdminUser\Application\Cli\UseCase\IssueRegistrationToken\IssueRegistrationTokenUseCase;
use AdminUser\Domain\Models\Permission;
use AdminUser\Domain\Models\RegistrationToken\ConsumptionStatus;
use AdminUser\Domain\Models\Role;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;

class IssueRegistrationTokenUseCaseTest extends DatabaseTestCase
{
    #[Test]
    public function canIssue(): void
    {
        $result = $this->getInstance()->handle(
            new IssueRegistrationTokenInputData('invitee@example.com', Role::General->value, []),
        );

        $rows = DB::table('admin_user_registration_tokens')->get()->all();
        $this->assertCount(1, $rows);

        $row = array_first($rows);
        $plain = $result->plainToken;

        $this->assertNotSame($plain, $row->token);
        $this->assertTrue(hash_equals(hash('sha256', $plain), $row->token));
        $this->assertSame('invitee@example.com', $row->email);
        $this->assertSame(Role::General->value, (int)$row->role);
        $this->assertSame(ConsumptionStatus::Unused->value, (int)$row->status);
        $this->assertGreaterThan(now()->format('Y-m-d H:i:s'), $row->expired_at);
        $this->assertSame(0, DB::table('admin_user_registration_token_permissions')->count());
    }

    #[Test]
    public function canIssueWithPrivilegeAndPermissions(): void
    {
        $result = $this->getInstance()->handle(
            new IssueRegistrationTokenInputData(
                'priv@example.com',
                Role::Privilege->value,
                [Permission::ReadAdminUser->value, Permission::WriteAdminUser->value],
            ),
        );

        $rows = DB::table('admin_user_registration_tokens')->get()->all();
        $this->assertCount(1, $rows);

        $row = array_first($rows);
        $this->assertSame(Role::Privilege->value, (int)$row->role);

        $permissions = DB::table('admin_user_registration_token_permissions')
            ->where('admin_user_registration_token_id', $row->admin_user_registration_token_id)
            ->pluck('permission')
            ->all();

        $this->assertEqualsCanonicalizing(
            [Permission::ReadAdminUser->value, Permission::WriteAdminUser->value],
            $permissions,
        );
    }

    private function getInstance(): IssueRegistrationTokenUseCase
    {
        return $this->app->make(IssueRegistrationTokenUseCase::class);
    }
}
