<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Tokenizer;

use ju1ius\HtmlParser\Tokenizer\Token;
use ju1ius\HtmlParser\Tokenizer\TokenizerStates;
use PHPUnit\Framework\TestCase;

class RCDataTest extends TestCase
{
    /**
     * @dataProvider rcdataProvider
     * @param string $input
     * @param array $expected
     */
    public function testRCData(string $input, array $expected)
    {
        TokenizerAssert::tokensEquals($input, $expected, null, TokenizerStates::RCDATA);
    }

    public function rcdataProvider()
    {
        yield [
            'Foo & Bar', [
                'Foo ',
                '&',
                ' Bar',
            ]
        ];
    }
}
