<?php

declare(strict_types=1);

namespace Tests\Unit\Tools\PHPStan\Rules\Data;

/**
 * @phpstan-type _valid array{id: string}
 * @phpstan-type _validCamel array{id: string}
 * @phpstan-type lowerCamel array{id: string}
 * @phpstan-type UpperCamel array{id: string}
 * @phpstan-type _UpperCamel array{id: string}
 * @phpstan-type _snake_case array{id: string}
 */
final class TypeAliasDefinitions
{
}

/**
 * @phpstan-import-type _valid from TypeAliasDefinitions
 * @phpstan-import-type _validCamel from TypeAliasDefinitions as _renamed
 * @phpstan-import-type _validCamel from TypeAliasDefinitions as Renamed
 */
final class TypeAliasImports
{
}
