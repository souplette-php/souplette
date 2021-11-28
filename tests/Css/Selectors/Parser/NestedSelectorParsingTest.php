<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Parser;

use PHPUnit\Framework\Assert;
use Souplette\Css\Selectors\Node\Functional\Is;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Node\Simple\ClassSelector;
use Souplette\Css\Selectors\Node\Simple\TypeSelector;
use Souplette\Tests\Css\Selectors\SelectorParserTestCase;
use Souplette\Tests\Css\Selectors\SelectorUtils;

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
            ]
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
            ]
        ];
    }
}
