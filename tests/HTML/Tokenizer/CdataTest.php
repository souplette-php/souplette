<?php declare(strict_types=1);

namespace Souplette\Tests\HTML\Tokenizer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\HTML\Tokenizer\Token;

class CdataTest extends TestCase
{
    /**
     * @param string $input
     * @param array $expected
     */
    #[DataProvider('cdataInHtmlProvider')]
    public function testCdataInHtml(string $input, array $expected)
    {
        TokenizerAssert::tokensEquals($input, $expected);
    }

    public static function cdataInHtmlProvider(): iterable
    {
        // CDATA in html is treated as a bogus comment
        yield ['<![CDATA[ foo ]]>', [Token::comment('[CDATA[ foo ]]')]];
    }
}
