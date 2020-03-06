<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Tokenizer;

use ju1ius\HtmlParser\Tokenizer\Token;
use PHPUnit\Framework\TestCase;

class StartTagTest extends TestCase
{
    /**
     * @dataProvider startTagProvider
     * @param string $input
     * @param array $expected
     */
    public function testStartTag(string $input, array $expected)
    {
        TokenizerAssert::tokensEquals($input, $expected);
    }

    public function startTagProvider()
    {
        yield ['<a>', [Token::startTag('a')]];
        yield ['<A>', [Token::startTag('a')]];
        yield ['<bé>', [Token::startTag('bé')]];
        yield ['<br/>', [Token::startTag('br', true)]];
        yield ['<br />', [Token::startTag('br', true)]];
    }

    /**
     * @dataProvider attributesProvider
     * @param string $input
     * @param array $expected
     */
    public function testAttributes(string $input, array $expected)
    {
        TokenizerAssert::tokensEquals($input, $expected);
    }

    public function attributesProvider()
    {
        yield ['<a b c>', [Token::startTag('a', false, [['b', ''], ['c', '']])]];
        yield ['<a b=c>', [Token::startTag('a', false, [['b', 'c']])]];
        yield ['<a b="c">', [Token::startTag('a', false, [['b', 'c']])]];
        yield ["<a b='c'>", [Token::startTag('a', false, [['b', 'c']])]];
    }

}
