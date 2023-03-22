<?php declare(strict_types=1);

namespace Souplette\Tests\HTML\Tokenizer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\HTML\Tokenizer\Token;

class TokenizerTest extends TestCase
{
    /**
     * @param string $input
     * @param array $expected
     */
    #[DataProvider('characterDataInElementProvider')]
    public function testCharacterDataInElement(string $input, array $expected)
    {
        TokenizerAssert::tokensEquals($input, $expected);
    }

    public static function characterDataInElementProvider(): iterable
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
