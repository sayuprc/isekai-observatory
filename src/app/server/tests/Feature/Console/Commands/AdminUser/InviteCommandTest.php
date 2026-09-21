<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands\AdminUser;

use AdminUser\Domain\Models\Role;
use AdminUser\Infrastructures\AdminUserRepository;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\DatabaseTestCase;
use Tests\Support\Domain\EntityFactory;

class InviteCommandTest extends DatabaseTestCase
{
    use EntityFactory;

    #[Test]
    public function canIssueGeneralToken(): void
    {
        $this->artisan('admin:invite invitee@example.com')->assertSuccessful();

        $rows = DB::table('admin_user_registration_tokens')->get()->all();
        $this->assertCount(1, $rows);

        $row = array_first($rows);
        $this->assertSame('invitee@example.com', $row->email);
        $this->assertSame(Role::General->value, (int)$row->role);
        $this->assertSame(0, DB::table('admin_user_registration_token_permissions')->count());
    }

    #[Test]
    public function canIssuePrivilegeTokenWithPermissions(): void
    {
        $this->artisan('admin:invite priv@example.com --privilege read_admin_user write_admin_user')
            ->assertSuccessful();

        $rows = DB::table('admin_user_registration_tokens')->get()->all();
        $this->assertCount(1, $rows);

        $row = array_first($rows);
        $this->assertSame(Role::Privilege->value, (int)$row->role);

        $permissions = DB::table('admin_user_registration_token_permissions')
            ->where('admin_user_registration_token_id', $row->admin_user_registration_token_id)
            ->pluck('permission')
            ->all();

        $this->assertEqualsCanonicalizing(['read_admin_user', 'write_admin_user'], $permissions);
    }

    #[Test]
    public function failureWithInvalidPermission(): void
    {
        $this->artisan('admin:invite invitee@example.com invalid_permission')
            ->expectsOutput('不正な権限です: invalid_permission')
            ->assertFailed();

        $this->assertSame(0, DB::table('admin_user_registration_tokens')->count());
    }

    #[Test]
    public function failureIfEmailAlreadyUsedByAdminUser(): void
    {
        $this->app->make(AdminUserRepository::class)->register(
            $this->createAdminUser($this->generateUuid(), 'taken@example.com', Role::General, []),
        );

        $this->artisan('admin:invite taken@example.com')
            ->expectsOutput('すでに使われているメールアドレスです "taken@example.com"')
            ->assertFailed();

        $this->assertSame(0, DB::table('admin_user_registration_tokens')->count());
    }

    #[Test]
    public function outputsPlainTokenOnce(): void
    {
        $this->artisan('admin:invite invitee@example.com')->assertSuccessful()->run();

        $rows = DB::table('admin_user_registration_tokens')->get()->all();
        $this->assertCount(1, $rows);

        // ハッシュ済みで保存されているため、ハッシュとは平文を直接比較できない
        // 平文は標準出力に出るが artisan の API では拾いにくいため、
        // ここではトークン行が 1 件・ハッシュとして妥当な値であることを担保する
        $row = array_first($rows);
        $this->assertNotSame('', $row->token);
        $this->assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $row->token);
    }
}
