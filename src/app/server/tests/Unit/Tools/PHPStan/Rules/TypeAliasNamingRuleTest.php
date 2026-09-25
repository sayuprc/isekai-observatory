<?php

declare(strict_types=1);

namespace Tests\Unit\Tools\PHPStan\Rules;

use Override;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Test;
use Tools\PHPStan\Rules\TypeAliasNamingRule;

/**
 * @extends RuleTestCase<TypeAliasNamingRule>
 */
class TypeAliasNamingRuleTest extends RuleTestCase
{
    #[Test]
    public function reportsAliasNamesNotInLowerCamelWithUnderscore(): void
    {
        $this->analyse([__DIR__ . '/data/type-alias-naming.php'], [
            ['型エイリアス名 lowerCamel は _lowerCamel 形式にしてください', 15],
            ['型エイリアス名 UpperCamel は _lowerCamel 形式にしてください', 15],
            ['型エイリアス名 _UpperCamel は _lowerCamel 形式にしてください', 15],
            ['型エイリアス名 _snake_case は _lowerCamel 形式にしてください', 15],
            ['型エイリアス名 Renamed は _lowerCamel 形式にしてください', 24],
        ]);
    }

    #[Override]
    protected function getRule(): Rule
    {
        return new TypeAliasNamingRule();
    }
}
