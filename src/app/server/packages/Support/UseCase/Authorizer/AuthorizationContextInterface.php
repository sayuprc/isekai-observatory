<?php

declare(strict_types=1);

namespace Support\UseCase\Authorizer;

interface AuthorizationContextInterface
{
    public function currentUser(): ?AuthorizableUserInterface;
}
