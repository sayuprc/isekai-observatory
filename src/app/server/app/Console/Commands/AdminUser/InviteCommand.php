<?php

declare(strict_types=1);

namespace App\Console\Commands\AdminUser;

use AdminUser\Application\Cli\UseCase\IssueRegistrationToken\IssueRegistrationTokenInputData;
use AdminUser\Application\Cli\UseCase\IssueRegistrationToken\IssueRegistrationTokenUseCase;
use AdminUser\Domain\Models\Permission;
use AdminUser\Domain\Models\Role;
use App\Console\Commands\Concerns\ResolvesUseCaseExceptionMessage;
use Illuminate\Console\Command;
use Override;
use Support\Domain\Exceptions\BusinessRuleViolationException;
use Support\Domain\Exceptions\InvalidDomainException;
use Support\UseCase\Exceptions\UseCaseException;

class InviteCommand extends Command
{
    use ResolvesUseCaseExceptionMessage;

    #[Override]
    protected $signature = 'admin:invite {email} {--p|privilege} {permissions?*}';

    #[Override]
    protected $description = '管理ユーザー登録トークンを発行する';

    public function handle(IssueRegistrationTokenUseCase $useCase): int
    {
        $email = $this->argument('email');

        if (mb_trim($email) === '') {
            $this->error('メールアドレスを入力してください');

            return Command::FAILURE;
        }

        $role = $this->isPrivilege()
            ? Role::Privilege
            : Role::General;

        $permissions = $this->argument('permissions');
        assert(array_is_list($permissions));

        foreach ($permissions as $permission) {
            $result = Permission::tryFrom($permission);
            if (is_null($result)) {
                $this->error("不正な権限です: {$permission}");

                return Command::FAILURE;
            }
        }

        try {
            $output = $useCase->handle(new IssueRegistrationTokenInputData($email, $role->value, $permissions));
        } catch (BusinessRuleViolationException|InvalidDomainException|UseCaseException $e) {
            $this->error($this->resolveExceptionMessage($e));

            return Command::FAILURE;
        }

        $this->line($output->plainToken);
        $this->info(sprintf('有効期限: %s', $output->token->expiredAt->value->format('Y-m-d H:i:s')));

        return Command::SUCCESS;
    }

    private function isPrivilege(): bool
    {
        return (bool)$this->option('privilege');
    }
}
