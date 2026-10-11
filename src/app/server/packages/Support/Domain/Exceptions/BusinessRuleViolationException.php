<?php

declare(strict_types=1);

namespace Support\Domain\Exceptions;

use RuntimeException;

/**
 * ドメインの業務ルール違反を表す例外
 *
 * message は利用者に表示される前提で書く
 */
class BusinessRuleViolationException extends RuntimeException
{
}
