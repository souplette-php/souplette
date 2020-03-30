<?php declare(strict_types=1);

namespace JoliPotage\Tests\Html\Parser\Tokenizer;

use JoliPotage\Html\Parser\Tokenizer\Token;
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
