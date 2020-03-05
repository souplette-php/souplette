<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Parser;

use ju1ius\HtmlParser\Parser\Entities\EntityLookup;
use ju1ius\HtmlParser\Parser\Token;
use ju1ius\HtmlParser\Parser\Tokenizer;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class TokenizerTest extends TestCase
{
    /**
     * @param string $input
     * @param Token[] $expected
     */
    private static function assertTokensEquals(string $input, array $expected)
    {
        $tokenizer = new Tokenizer($input);
        $actual = iterator_to_array($tokenizer->tokenize());
        Assert::assertEquals($expected, $actual);
    }

    /**
     * @dataProvider characterDataProvider
     * @param string $input
     * @param array $expected
     */
    public function testCharacterData(string $input, array $expected)
    {
        self::assertTokensEquals($input, $expected);
    }

    public function characterDataProvider()
    {
        yield [
            'foo',
            [Token::character('foo')],
        ];
    }

    /**
     * @dataProvider startTagProvider
     * @param string $input
     * @param array $expected
     */
    public function testStartTag(string $input, array $expected)
    {
        self::assertTokensEquals($input, $expected);
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
        self::assertTokensEquals($input, $expected);
    }

    public function attributesProvider()
    {
        yield ['<a b c>', [Token::startTag('a', false, [['b', ''], ['c', '']])]];
        yield ['<a b=c>', [Token::startTag('a', false, [['b', 'c']])]];
        yield ['<a b="c">', [Token::startTag('a', false, [['b', 'c']])]];
        yield ["<a b='c'>", [Token::startTag('a', false, [['b', 'c']])]];
    }

    /**
     * @dataProvider endTagProvider
     * @param string $input
     * @param array $expected
     */
    public function testEndTag(string $input, array $expected)
    {
        self::assertTokensEquals($input, $expected);
    }

    public function endTagProvider()
    {
        yield ['</foo>', [Token::endTag('foo')]];
        yield ['</foo >', [Token::endTag('foo')]];
    }

    /**
     * @dataProvider entitiesProvider
     * @param string $input
     * @param array $expected
     */
    public function testEntities(string $input, array $expected)
    {
        self::assertTokensEquals($input, $expected);
    }

    public function entitiesProvider()
    {
        yield ['&amp;', [Token::character(EntityLookup::NAMED_ENTITIES['amp;'])]];
        // See examples at https://html.spec.whatwg.org/multipage/parsing.html#named-character-reference-state
        yield ["I'm &notit; I tell you", [
            Token::character("I'm "),
            Token::character(EntityLookup::NAMED_ENTITIES['not']),
            Token::character("it; I tell you"),
        ]];
        yield ["I'm &notin; I tell you", [
            Token::character("I'm "),
            Token::character(EntityLookup::NAMED_ENTITIES['notin;']),
            Token::character(" I tell you"),
        ]];
        yield ['&#38;', [Token::character('&')]];
        yield ['&#x26;', [Token::character('&')]];
    }

    /**
     * @dataProvider commentsProvider
     * @param string $input
     * @param array $expected
     */
    public function testComments(string $input, array $expected)
    {
        self::assertTokensEquals($input, $expected);
    }

    public function commentsProvider()
    {
        yield ['<!-- comment -->', [Token::comment(' comment ')]];
    }

    /**
     * @dataProvider doctypeProvider
     * @param string $input
     * @param array $expected
     */
    public function testDoctype(string $input, array $expected)
    {
        self::assertTokensEquals($input, $expected);
    }

    public function doctypeProvider()
    {
        yield ['<!DOCTYPE html>', [Token::doctype('html')]];
    }
}
