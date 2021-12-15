<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Parser;

use Souplette\CSS\Selectors\Node\SelectorList;
use Souplette\CSS\Selectors\Node\Simple\AttributeSelector;
use Souplette\CSS\Selectors\Node\Simple\ClassSelector;
use Souplette\CSS\Selectors\Node\Simple\IdSelector;
use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\CSS\Selectors\Node\Simple\PseudoElementSelector;
use Souplette\CSS\Selectors\Node\Simple\TypeSelector;
use Souplette\CSS\Selectors\Node\SimpleSelector;
use Souplette\Tests\CSS\Selectors\SelectorAssert;
use Souplette\Tests\CSS\Selectors\SelectorParserTestCase;
use Souplette\Tests\CSS\Selectors\SelectorUtils;

final class CompoundSelectorParsingTest extends SelectorParserTestCase
{
    /**
     * @dataProvider parseSelectorListWithCompoundSelectorsProvider
     * @param string $input
     * @param SimpleSelector[] $expected
     */
    public function testParseSelectorListWithCompoundSelectors(string $input, array $expected)
    {
        $selector = SelectorUtils::parseSelectorList($input);
        $expected = new SelectorList([
            SelectorUtils::compoundToComplex($expected),
        ]);
        SelectorAssert::selectorListEquals($expected, $selector);
    }

    public function parseSelectorListWithCompoundSelectorsProvider(): iterable
    {
        yield 'foo.bar' => [
            'foo.bar',
            [new TypeSelector('foo', '*'), new ClassSelector('bar')],
        ];
        yield '#foo.bar' => [
            '#foo.bar',
            [new IdSelector('foo'), new ClassSelector('bar')],
        ];
        yield '.foo:bar' => [
            '.foo:bar',
            [new ClassSelector('foo'), new PseudoClassSelector('bar')],
        ];
        yield '::bar:baz' => [
            '::bar:baz',
            [new PseudoElementSelector('bar'), new PseudoClassSelector('baz')],
        ];
        yield 'a[href]::before:hover:first-letter' => [
            'a[href]::before:hover:first-letter',
            [
                new TypeSelector('a', '*'),
                new AttributeSelector('href'),
                new PseudoElementSelector('before'),
                new PseudoClassSelector('hover'),
                new PseudoElementSelector('first-letter'),
            ],
        ];
    }
}
