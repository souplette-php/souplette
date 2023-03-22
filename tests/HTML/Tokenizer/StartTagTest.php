<?php declare(strict_types=1);

namespace Souplette\Tests\HTML\Tokenizer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\HTML\Tokenizer\Token;

class StartTagTest extends TestCase
{
    /**
     * @param string $input
     * @param array $expected
     */
    #[DataProvider('startTagProvider')]
    public function testStartTag(string $input, array $expected)
    {
        TokenizerAssert::tokensEquals($input, $expected);
    }

    public static function startTagProvider(): iterable
    {
        yield ['<a>', [Token::startTag('a')]];
        yield ['<A>', [Token::startTag('a')]];
        yield ['<bé>', [Token::startTag('bé')]];
        yield ['<br/>', [Token::startTag('br', true)]];
        yield ['<br />', [Token::startTag('br', true)]];
        yield ['<a><b><c>', [Token::startTag('a'), Token::startTag('b'), Token::startTag('c')]];

    }

    /**
     * @param string $input
     * @param array $expected
     */
    #[DataProvider('attributesProvider')]
    public function testAttributes(string $input, array $expected)
    {
        TokenizerAssert::tokensEquals($input, $expected);
    }

    public static function attributesProvider(): iterable
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
