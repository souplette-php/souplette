<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Parser;

use PHPUnit\Framework\Assert;
use Souplette\CSS\Selectors\Node\Functional\Is;
use Souplette\CSS\Selectors\Node\Functional\Where;
use Souplette\CSS\Selectors\Node\SelectorList;
use Souplette\CSS\Selectors\Node\Simple\ClassSelector;
use Souplette\CSS\Selectors\Node\Simple\TypeSelector;
use Souplette\Tests\CSS\Selectors\SelectorParserTestCase;
use Souplette\Tests\CSS\Selectors\SelectorUtils;

final class NestedSelectorParsingTest extends SelectorParserTestCase
{
    /**
     * @dataProvider parseIsProvider
     */
    public function testParseIs(string $selectorText, array $expected)
    {
        $selector = SelectorUtils::parseSelectorList($selectorText);
        $expected = new SelectorList([
            SelectorUtils::simpleToComplex(new Is(new SelectorList($expected))),
        ]);
        Assert::assertEquals($expected, $selector);
    }

    public function parseIsProvider(): iterable
    {
        yield ':is(foo)' => [
            ':is(foo)',
            [
                SelectorUtils::simpleToComplex(new TypeSelector('foo', '*')),
            ],
        ];
        yield ':is(foo, .bar)' => [
            ':is(foo, .bar)',
            [
                SelectorUtils::simpleToComplex(new TypeSelector('foo', '*')),
                SelectorUtils::simpleToComplex(new ClassSelector('bar')),
            ],
        ];
        yield 'forgiving :is(?, foo)' => [
            ':is(?, foo)',
            [
                SelectorUtils::simpleToComplex(new TypeSelector('foo', '*')),
            ],
        ];
        yield 'forgiving :is(?, &&&)' => [':is(?, &&&)', []];
    }

    /**
     * @dataProvider parseWhereProvider
     */
    public function testParseWhere(string $selectorText, array $expected)
    {
        $selector = SelectorUtils::parseSelectorList($selectorText);
        $expected = new SelectorList([
            SelectorUtils::simpleToComplex(new Where(new SelectorList($expected))),
        ]);
        Assert::assertEquals($expected, $selector);
    }

    public function parseWhereProvider(): iterable
    {
        yield ':where(foo)' => [
            ':where(foo)',
            [
                SelectorUtils::simpleToComplex(new TypeSelector('foo', '*')),
            ],
        ];
        yield ':where(foo, .bar)' => [
            ':where(foo, .bar)',
            [
                SelectorUtils::simpleToComplex(new TypeSelector('foo', '*')),
                SelectorUtils::simpleToComplex(new ClassSelector('bar')),
            ],
        ];
        yield 'forgiving :where(?, foo)' => [
            ':where(?, foo)',
            [
                SelectorUtils::simpleToComplex(new TypeSelector('foo', '*')),
            ],
        ];
        yield 'forgiving :where(?, &&&)' => [':where(?, &&&)', []];
    }
}
