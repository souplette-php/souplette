<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Tokenizer;

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

    public function characterInDataProvider()
    {
        yield [
            'foo',
            ['foo'],
        ];
    }
}
