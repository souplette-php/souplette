<?php declare(strict_types=1);

namespace Souplette\Tests\Html\Parser\Tokenizer;

use PHPUnit\Framework\TestCase;
use Souplette\Html\Parser\Tokenizer\Token;

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
