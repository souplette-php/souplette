<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Parser;

use PHPUnit\Framework\Assert;
use Souplette\Css\Selectors\Node\AttributeSelector;
use Souplette\Css\Selectors\Node\ClassSelector;
use Souplette\Css\Selectors\Node\ComplexSelector;
use Souplette\Css\Selectors\Node\CompoundSelector;
use Souplette\Css\Selectors\Node\IdSelector;
use Souplette\Css\Selectors\Node\PseudoClassSelector;
use Souplette\Css\Selectors\Node\PseudoElementSelector;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Node\TypeSelector;
use Souplette\Tests\Css\Selectors\SelectorParserTestCase;

final class CompoundSelectorParsingTest extends SelectorParserTestCase
{
    /**
     * @dataProvider parseSelectorListWithCompoundSelectorsProvider
     * @param string $input
     * @param $expected
     */
    public function testParseSelectorListWithCompoundSelectors(string $input, array $expected)
    {
        $selector = self::parseSelectorList($input);
        $expected = new SelectorList([new ComplexSelector(new CompoundSelector($expected))]);
        Assert::assertEquals($expected, $selector);
    }

    public function parseSelectorListWithCompoundSelectorsProvider(): \Generator
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
