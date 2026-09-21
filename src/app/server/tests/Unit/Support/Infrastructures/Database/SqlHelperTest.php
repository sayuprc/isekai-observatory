<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Infrastructures\Database;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\Infrastructures\Database\SqlHelper;
use Tests\TestCase;

class SqlHelperTest extends TestCase
{
    #[Test]
    public function escapeLikeWithNormalString(): void
    {
        $result = SqlHelper::escapeLike('test');

        $this->assertSame('test', $result);
    }

    #[Test]
    #[DataProvider('provideLikeSpecialCharacters')]
    public function escapeLikeWithSpecialCharacters(string $input, string $expected): void
    {
        $result = SqlHelper::escapeLike($input);

        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{input: string, expected: string}>
     */
    public static function provideLikeSpecialCharacters(): array
    {
        return [
            'percent sign' => [
                'input' => 'test%',
                'expected' => 'test\%',
            ],
            'underscore' => [
                'input' => 'test_',
                'expected' => 'test\_',
            ],
            'backslash' => [
                'input' => 'test\\',
                'expected' => 'test\\\\',
            ],
            'multiple percent signs' => [
                'input' => '%test%',
                'expected' => '\%test\%',
            ],
            'multiple underscores' => [
                'input' => 'test_name_here',
                'expected' => 'test\_name\_here',
            ],
            'mixed special characters' => [
                'input' => 'test%_\\value',
                'expected' => 'test\%\_\\\\value',
            ],
            'all special characters together' => [
                'input' => '%_\\',
                'expected' => '\%\_\\\\',
            ],
            'Japanese characters with special chars' => [
                'input' => 'テスト%文字列',
                'expected' => 'テスト\%文字列',
            ],
        ];
    }

    #[Test]
    public function escapeLikeWithEmptyString(): void
    {
        $result = SqlHelper::escapeLike('');

        $this->assertSame('', $result);
    }

    #[Test]
    public function escapeLikePreservesOtherCharacters(): void
    {
        $input = 'Hello World! 123 @#$ あいうえお';
        $result = SqlHelper::escapeLike($input);

        $this->assertSame($input, $result);
    }
}
