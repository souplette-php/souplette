<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Souplette\CSS\Selectors\Node\Functional\Not;
use Souplette\CSS\Selectors\Node\SelectorList;
use Souplette\CSS\Selectors\Node\Simple\ClassSelector;
use Souplette\CSS\Selectors\Node\Simple\IdSelector;
use Souplette\CSS\Selectors\Node\Simple\TypeSelector;
use Souplette\CSS\Selectors\Specificity;
use Souplette\DOM\Element;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\CSS\Selectors\SelectorTestCase;
use Souplette\Tests\CSS\Selectors\SelectorUtils;
use Souplette\Tests\DOM\DOMBuilder;

final class NotTest extends SelectorTestCase
{
    public static function toStringProvider(): iterable
    {
        yield [
            new Not(SelectorUtils::toSelectorList([
                new TypeSelector('foo', '*'),
                new TypeSelector('bar', '*'),
            ])),
            ':not(foo, bar)',
        ];
    }

    public static function specificityProvider(): iterable
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

    #[DataProvider('matchesProvider')]
    public function testMatches(Element $element, Not $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public static function matchesProvider(): iterable
    {
        $dom = DOMBuilder::html()->tag('html')
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
