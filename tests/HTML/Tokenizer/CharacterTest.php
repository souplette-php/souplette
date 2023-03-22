<?php declare(strict_types=1);

namespace Souplette\Tests\HTML\Tokenizer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CharacterTest extends TestCase
{
    /**
     * @param string $input
     * @param array $expected
     */
    #[DataProvider('characterInDataProvider')]
    public function testCharacterInData(string $input, array $expected)
    {
        TokenizerAssert::tokensEquals($input, $expected);
    }

    public static function characterInDataProvider(): iterable
    {
        yield [
            'foo',
            ['foo'],
        ];
    }
}
