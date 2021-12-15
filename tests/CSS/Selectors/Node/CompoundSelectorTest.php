<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node;

use Souplette\DOM\Element;
use PHPUnit\Framework\TestCase;
use Souplette\CSS\Selectors\Node\Selector;
use Souplette\CSS\Selectors\Node\Simple\ClassSelector;
use Souplette\CSS\Selectors\Node\Simple\TypeSelector;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\CSS\Selectors\SelectorUtils;
use Souplette\Tests\DOM\DOMBuilder;
use function in_array;

final class CompoundSelectorTest extends TestCase
{
    /**
     * @dataProvider itMatchesClassesProvider
     */
    public function testItMatchesClasses(Element $element, Selector $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function itMatchesClassesProvider(): iterable
    {
        $dom = DOMBuilder::html()->tag('html')
            ->tag('a')->class('a')->close()
            ->tag('a')->class('a b')->close()
            ->tag('a')->class('a b c')->close()
            ->getDocument();

        $provider = function(array $indices, Selector $selector) use ($dom) {
            foreach ($dom->documentElement->childNodes as $index => $node) {
                $mustMatch = in_array($index, $indices, true);
                $key = sprintf(
                    "node n°%d %s %s",
                    $index,
                    $mustMatch ? 'matches' : 'does not match',
                    $selector,
                );
                yield $key => [$node, $selector, $mustMatch];
            }
        };

        yield from $provider(
            [0, 1, 2],
            SelectorUtils::compoundToComplex([
                new TypeSelector('a', '*'),
                new ClassSelector('a'),
            ]),
        );
        yield from $provider(
            [1, 2],
            SelectorUtils::compoundToComplex([
                new TypeSelector('a', '*'),
                new ClassSelector('a'),
                new ClassSelector('b'),
            ]),
        );
        yield from $provider(
            [2],
            SelectorUtils::compoundToComplex([
                new ClassSelector('a'),
                new ClassSelector('b'),
                new ClassSelector('c'),
            ]),
        );
        yield from $provider(
            [],
            SelectorUtils::compoundToComplex([
                new ClassSelector('a'),
                new ClassSelector('nope'),
            ]),
        );
    }
}
