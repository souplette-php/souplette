<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Tokenizer;

use ju1ius\HtmlParser\Tokenizer\Token;
use PHPUnit\Framework\TestCase;

class TokenizerTest extends TestCase
{
    /**
     * @dataProvider characterDataInElementProvider
     * @param string $input
     * @param array $expected
     */
    public function testCharacterDataInElement(string $input, array $expected)
    {
        TokenizerAssert::tokensEquals($input, $expected);
    }

    public function characterDataInElementProvider()
    {
        yield [
            '<title>The New York Times - Breaking News, World News &amp; Multimedia</title>',
            [
                Token::startTag('title'),
                'The New York Times - Breaking News, World News ',
                '&',
                ' Multimedia',
                Token::endTag('title'),
            ]
        ];
    }
}
