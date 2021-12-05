<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\Functional;

use Souplette\Dom\Element;
use Souplette\Css\Selectors\Node\Functional\NthLastChild;
use Souplette\Css\Selectors\Node\Simple\ClassSelector;
use Souplette\Css\Selectors\Node\Simple\IdSelector;
use Souplette\Css\Selectors\Node\Simple\TypeSelector;
use Souplette\Css\Selectors\Specificity;
use Souplette\Css\Syntax\Node\AnPlusB;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Css\Selectors\SelectorTestCase;
use Souplette\Tests\Css\Selectors\SelectorUtils;
use Souplette\Tests\Dom\DomBuilder;

final class NthLastChildTest extends SelectorTestCase
{
    public function toStringProvider(): iterable
    {
        yield [new NthLastChild(new AnPlusB(0, 1)), ':nth-last-child(1)'];
        yield [
            new NthLastChild(new AnPlusB(2, 1), SelectorUtils::toSelectorList([
                new TypeSelector('a', '*'),
                new ClassSelector('b'),
                new IdSelector('c'),
            ])),
            ':nth-last-child(odd of a, .b, #c)',
        ];
    }

    public function specificityProvider(): iterable
    {
        yield [
            new NthLastChild(new AnPlusB(2, 1)),
            new Specificity(0, 1, 0),
        ];
        yield [
            new NthLastChild(new AnPlusB(2, 1), SelectorUtils::toSelectorList([
                new TypeSelector('a', '*'),
                new ClassSelector('b'),
                new IdSelector('c'),
            ])),
            new Specificity(1, 1, 0),
        ];
    }

    /**
     * @dataProvider simpleAnPlusBProvider
     */
    public function testSimpleAnPlusB(Element $element, NthLastChild $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function simpleAnPlusBProvider(): iterable
    {
        $dom = DomBuilder::create()->tag('html')
            ->tag('a')->close()
            ->tag('a')->close()
            ->tag('a')->close()
            ->getDocument();
        $nodes = $dom->documentElement->children;
        foreach (array_reverse($nodes) as $i => $node) {
            $b = $i + 1;
            yield "matches :nth-last-child({$b})" => [
                $node,
                new NthLastChild(new AnPlusB(0, $b)),
                true,
            ];
        }
    }

    /**
     * @dataProvider aNPlusBProvider
     */
    public function testAnPlusB(Element $element, NthLastChild $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function aNPlusBProvider(): iterable
    {
        $dom = DomBuilder::create()->tag('html')
            ->tag('a')->close()
            ->tag('a')->close()
            ->tag('a')->close()
            ->tag('a')->close()
            ->tag('a')->close()
            ->tag('a')->close()
            ->getDocument();

        $provider = static function(int $a, int $b, array $indices) use ($dom) {
            $selector = new NthLastChild(new AnPlusB($a, $b));
            foreach ($dom->documentElement->children as $index => $node) {
                $mustMatch = \in_array($index, $indices, true);
                $key = sprintf(
                    'child n°%d %s %s',
                    $index,
                    $mustMatch ? 'matches' : 'does not match',
                    $selector,
                );
                yield $key => [$node, $selector, $mustMatch];
            }
        };

        yield from $provider(2, 0, [0, 2, 4]);
        yield from $provider(2, 1, [1, 3, 5]);
        yield from $provider(3, 0, [0, 3]);
        yield from $provider(3, 1, [2, 5]);
        yield from $provider(3, 2, [1, 4]);
    }
}
