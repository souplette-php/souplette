<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\Functional;

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
    public function testMatches(\DOMElement $element, Not $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function matchesProvider(): iterable
    {
        $dom = DomBuilder::create()
            ->tag('a')->close()
            ->tag('b')->close()
            ->tag('a')->close()
            ->tag('b')->close()
            ->getDocument();
        foreach ($dom->childNodes as $i => $node) {
            foreach (['a', 'b'] as $tagName) {
                $mustMatch = $node->tagName !== $tagName;
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
