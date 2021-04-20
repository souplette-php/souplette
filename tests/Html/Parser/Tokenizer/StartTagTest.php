<?php declare(strict_types=1);

namespace Souplette\Tests\Html\Parser\Tokenizer;

use PHPUnit\Framework\TestCase;
use Souplette\Html\Parser\Tokenizer\Token;

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
        yield ['<a><b><c>', [Token::startTag('a'), Token::startTag('b'), Token::startTag('c')]];

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
        yield ['<a b c>', [Token::startTag('a', false, ['b' => '', 'c' => ''])]];
        yield ['<a b=c>', [Token::startTag('a', false, ['b' => 'c'])]];
        yield ['<a b="c">', [Token::startTag('a', false, ['b' => 'c'])]];
        yield ["<a b='c'>", [Token::startTag('a', false, ['b' => 'c'])]];
        yield ['<a b><c d=e>', [
            Token::startTag('a', false, ['b' => '']),
            Token::startTag('c', false, ['d' => 'e'])
        ]];
        yield ['<a b="b" b="c">', [Token::startTag('a', false, ['b' => 'b'])]];
    }

}
