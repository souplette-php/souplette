<?php declare(strict_types=1);

namespace Souplette\Tests\HTML\Tokenizer;

use PHPUnit\Framework\TestCase;

class CharacterTest extends TestCase
{
    /**
     * @dataProvider characterInDataProvider
     * @param string $input
     * @param array $expected
     */
    public function testCharacterInData(string $input, array $expected)
    {
        TokenizerAssert::tokensEquals($input, $expected);
    }

    public function characterInDataProvider(): iterable
    {
        yield [
            'foo',
            ['foo'],
        ];
    }
}
