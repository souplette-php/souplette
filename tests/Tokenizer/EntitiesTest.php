<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Tokenizer;

use ju1ius\HtmlParser\Tokenizer\EntityLookup;
use ju1ius\HtmlParser\Tokenizer\Token;
use PHPUnit\Framework\TestCase;

class EntitiesTest extends TestCase
{
    /**
     * @dataProvider entitiesInDataProvider
     * @param string $input
     * @param array $expected
     */
    public function testEntitiesInData(string $input, array $expected)
    {
        TokenizerAssert::tokensEquals($input, $expected);
    }

    public function entitiesInDataProvider()
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
        yield ['&acirc;&#128;&#156;', [
            Token::character('â'),
            Token::character("\u{0080}"),
            Token::character("\u{009C}"),
        ]];
    }
}
