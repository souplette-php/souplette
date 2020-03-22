<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\Tests\Tokenizer;

use ju1ius\HtmlParser\Tokenizer\EntityLookup;
use ju1ius\HtmlParser\Tokenizer\ParseErrors;
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
        yield ['&amp;', ['&']];
        yield ['&Abreve', ['&', 'Abreve']];
        // See examples at https://html.spec.whatwg.org/multipage/parsing.html#named-character-reference-state
        $not = EntityLookup::NAMED_ENTITIES['not'];
        yield ["I'm &notit; I tell you", ["I'm ", $not, "it; I tell you"]];
        $notin = EntityLookup::NAMED_ENTITIES['notin;'];
        yield ["I'm &notin; I tell you", ["I'm ", $notin, " I tell you"]];
        yield ['&noti;', [$not, 'i;']];
        yield ['&#38;', ['&']];
        yield ['&#x26;', ['&']];
        yield ['&acirc;&#128;&#156;', ['â', "\u{20AC}", "\u{0153}"]];
        foreach (EntityLookup::NAMED_ENTITIES as $entity => $value) {
            yield ["&{$entity}", [$value]];
        }
    }

    /**
     * @dataProvider invalidEntitiesInDataProvider
     * @param string $input
     * @param array $expectedTokens
     * @param array $expectedErrors
     */
    public function testInvalidEntitiesInData(string $input, array $expectedTokens, array $expectedErrors = [])
    {
        TokenizerAssert::tokensEquals($input, $expectedTokens, $expectedErrors);
    }

    public function invalidEntitiesInDataProvider()
    {
        yield ['&test=', ['&', 'test', '=']];
        yield ['&foobar;', ['&', 'foobar', ';'], [
            [ParseErrors::UNKNOWN_NAMED_CHARACTER_REFERENCE, 7],
        ]];
        // Control character reference replacements
        foreach (EntityLookup::NUMERIC_CTRL_REPLACEMENTS as $char => $replacement) {
            $input = sprintf('&#%d;', $char);
            $output = \IntlChar::chr($replacement);
            $key = sprintf('Control char replacement: %s => \u{%X}', $input, $replacement);
            yield $key => [$input, [Token::character($output)], [
                [ParseErrors::CONTROL_CHARACTER_REFERENCE, strlen($input)],
            ]];
        }
        // Outside unicode range
        $cp = 0x10FFFF + 1;
        $input = sprintf('&#%d;', $cp);
        yield [$input, ["\u{FFFD}"], [
            [ParseErrors::CHARACTER_REFERENCE_OUTSIDE_UNICODE_RANGE, strlen($input)],
        ]];
        $input = sprintf('&#x%X;', $cp);
        yield [$input, ["\u{FFFD}"], [
            [ParseErrors::CHARACTER_REFERENCE_OUTSIDE_UNICODE_RANGE, strlen($input)],
        ]];
    }

    /**
     * @dataProvider entitiesInAttributeProvider
     * @param string $input
     * @param array $expectedTokens
     */
    public function testEntitiesInAttribute(string $input, array $expectedTokens)
    {
        TokenizerAssert::tokensEquals($input, $expectedTokens);
    }

    public function entitiesInAttributeProvider()
    {
        yield ['<a b="I\'m &notit; I tell you">', [
            Token::startTag('a', false, [
                'b' => "I'm &notit; I tell you"
            ])
        ]];
        yield ['<a b="I\'m &notin; I tell you">', [
            Token::startTag('a', false, [
                'b' => sprintf("I'm %s I tell you", EntityLookup::NAMED_ENTITIES['notin;'])
            ])
        ]];
        yield ["<h a='&noti;'>", [
            Token::startTag('h', false, ['a' => '&noti;'])
        ]];
        foreach (EntityLookup::NAMED_ENTITIES as $entity => $value) {
            yield ["<a b='&{$entity}'>", [Token::startTag('a', false, ['b' => $value])]];
            if ($entity[-1] !== ';') {
                yield ["<a b='&{$entity}='>", [Token::startTag('a', false, ['b' => "&{$entity}="])]];
            }
        }
    }
}
