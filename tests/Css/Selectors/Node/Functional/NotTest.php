<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\Functional;

use Souplette\Dom\Element;
use Souplette\Css\Selectors\Node\Functional\Not;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Node\Simple\ClassSelector;
use Souplette\Css\Selectors\Node\Simple\IdSelector;
use Souplette\Css\Selectors\Node\Simple\TypeSelector;
use Souplette\Css\Selectors\Specificity;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Css\Selectors\SelectorTestCase;
use Souplette\Tests\Css\Selectors\SelectorUtils;
use Souplette\Tests\Dom\DomBuilder;

final class NotTest extends SelectorTestCase
{
    public function toStringProvider(): iterable
    {
        yield [
            new Not(SelectorUtils::toSelectorList([
                new TypeSelector('foo', '*'),
                new TypeSelector('bar', '*'),
            ])),
            ':not(foo, bar)',
        ];
    }

    public function specificityProvider(): iterable
    {
        yield [
            new Not(SelectorUtils::toSelectorList([
                new TypeSelector('foo', '*'),
                new ClassSelector('bar'),
                new IdSelector('baz'),
            ])),
            new Specificity(1, 0, 0),
        ];
    }

    /**
     * @dataProvider matchesProvider
     */
    public function testMatches(Element $element, Not $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function matchesProvider(): iterable
    {
        $dom = DomBuilder::create()->tag('html')
            ->tag('a')->close()
            ->tag('b')->close()
            ->tag('a')->close()
            ->tag('b')->close()
            ->getDocument();
        foreach ($dom->documentElement->children as $i => $node) {
            foreach (['a', 'b'] as $tagName) {
                $mustMatch = $node->localName !== $tagName;
                $selector = new Not(new SelectorList([
                    new TypeSelector($tagName, '*'),
                ]));
                $key = sprintf(
                    'child n°%d %s selector %s',
                    $i,
                    $mustMatch ? 'matches' : 'does not match',
                    $selector,
                );
                yield $key => [
                    $node,
                    $selector,
                    $mustMatch,
                ];
            }
        }
    }
}
