<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Auth;

use Auth\Domain\Models\AuthContext;
use Support\UseCase\Authorizer\AuthorizableUserInterface;
use Support\UseCase\Authorizer\AuthorizationContextInterface;

readonly class UseCaseAuthorizationContext implements AuthorizationContextInterface
{
    public function __construct(private AuthContext $context)
    {
    }

    public function currentUser(): ?AuthorizableUserInterface
    {
        $user = $this->context->get();

        if (is_null($user)) {
            return null;
        }

        return new AuthorizedAdminUser($user);
    }
}
