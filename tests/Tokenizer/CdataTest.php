<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Tokenizer;

use ju1ius\HtmlParser\Tokenizer\Token;
use PHPUnit\Framework\TestCase;

class CdataTest extends TestCase
{
    /**
     * @dataProvider cdataInHtmlProvider
     * @param string $input
     * @param array $expected
     */
    public function testCdataInHtml(string $input, array $expected)
    {
        TokenizerAssert::tokensEquals($input, $expected);
    }

    public function cdataInHtmlProvider()
    {
        // CDATA in html is treated as a bogus comment
        yield ['<![CDATA[ foo ]]>', [Token::comment('[CDATA[ foo ]]')]];
    }
}
