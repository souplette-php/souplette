<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\Functional;

use Souplette\DOM\Element;
use Souplette\CSS\Selectors\Node\Functional\NthChild;
use Souplette\CSS\Selectors\Node\Simple\ClassSelector;
use Souplette\CSS\Selectors\Node\Simple\IdSelector;
use Souplette\CSS\Selectors\Node\Simple\TypeSelector;
use Souplette\CSS\Selectors\Specificity;
use Souplette\CSS\Syntax\Node\AnPlusB;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\CSS\Selectors\SelectorTestCase;
use Souplette\Tests\CSS\Selectors\SelectorUtils;
use Souplette\Tests\DOM\DOMBuilder;

final class NthChildTest extends SelectorTestCase
{
    public function toStringProvider(): iterable
    {
        yield [new NthChild(new AnPlusB(0, 1)), ':nth-child(1)'];
        yield [
            new NthChild(new AnPlusB(2, 1), SelectorUtils::toSelectorList([
                new TypeSelector('a', '*'),
                new ClassSelector('b'),
                new IdSelector('c'),
            ])),
            ':nth-child(odd of a, .b, #c)',
        ];
    }

    public function specificityProvider(): iterable
    {
        yield [
            new NthChild(new AnPlusB(2, 1)),
            new Specificity(0, 1, 0),
        ];
        yield [
            new NthChild(new AnPlusB(2, 1), SelectorUtils::toSelectorList([
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
    public function testSimpleAnPlusB(Element $element, NthChild $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function simpleAnPlusBProvider(): iterable
    {
        $dom = DOMBuilder::html()->tag('html')
            ->tag('a')->close()
            ->tag('a')->close()
            ->tag('a')->close()
            ->getDocument();
        foreach ($dom->documentElement->children as $i => $node) {
            $b = $i + 1;
            yield "matches :nth-child({$b})" => [
                $node,
                new NthChild(new AnPlusB(0, $b)),
                true,
            ];
        }
    }

    /**
     * @dataProvider aNPlusBProvider
     */
    public function testAnPlusB(Element $element, NthChild $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function aNPlusBProvider(): iterable
    {
        $dom = DOMBuilder::html()->tag('html')
            ->tag('a')->close()
            ->tag('a')->close()
            ->tag('a')->close()
            ->tag('a')->close()
            ->tag('a')->close()
            ->tag('a')->close()
            ->getDocument();

        $provider = function(int $a, int $b, array $indices) use ($dom) {
            $selector = new NthChild(new AnPlusB($a, $b));
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

        yield from $provider(2, 0, [1, 3, 5]);
        yield from $provider(2, 1, [0, 2, 4]);
        yield from $provider(3, 0, [2, 5]);
        yield from $provider(3, 1, [0, 3]);
        yield from $provider(3, 2, [1, 4]);
    }
}
