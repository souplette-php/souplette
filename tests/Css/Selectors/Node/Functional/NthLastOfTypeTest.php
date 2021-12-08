<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\Functional;

use Souplette\Dom\Element;
use Souplette\Css\Selectors\Node\Functional\NthLastOfType;
use Souplette\Css\Selectors\Specificity;
use Souplette\Css\Syntax\Node\AnPlusB;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Css\Selectors\SelectorTestCase;
use Souplette\Tests\Dom\DomBuilder;

final class NthLastOfTypeTest extends SelectorTestCase
{
    public function toStringProvider(): iterable
    {
        yield [new NthLastOfType(new AnPlusB(2, 1)), ':nth-last-of-type(odd)'];
    }

    public function specificityProvider(): iterable
    {
        yield [new NthLastOfType(new AnPlusB(2, 1)), new Specificity(0, 1, 0)];
    }
    /**
     * @dataProvider simpleAnPlusBProvider
     */
    public function testSimpleAnPlusB(Element $element, NthLastOfType $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function simpleAnPlusBProvider(): iterable
    {
        $dom = DomBuilder::html()->tag('html')
            ->tag('a')->close()
            ->tag('b')->close()
            ->tag('a')->close()
            ->tag('b')->close()
            ->tag('a')->close()
            ->tag('b')->close()
            ->getDocument();
        $indices = [];
        $nodes = $dom->documentElement->children;
        foreach (array_reverse($nodes) as $node) {
            $indices[$node->localName] ??= 1;
            $b = $indices[$node->localName]++;
            $key = sprintf('matches %s:nth-last-of-type(%d)', $node->localName, $b);
            yield $key => [
                $node,
                new NthLastOfType(new AnPlusB(0, $b)),
                true,
            ];
        }
    }

    /**
     * @dataProvider aNPlusBProvider
     */
    public function testAnPlusB(Element $element, NthLastOfType $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function aNPlusBProvider(): iterable
    {
        $dom = DomBuilder::html()->tag('html')
            ->tag('a')->close()
            ->tag('b')->close()
            ->tag('a')->close()
            ->tag('b')->close()
            ->tag('a')->close()
            ->tag('b')->close()
            ->getDocument();

        $provider = static function(int $a, int $b, array $indices) use ($dom) {
            $selector = new NthLastOfType(new AnPlusB($a, $b));
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

        yield from $provider(2, 0, [2, 3]);
        yield from $provider(2, 1, [0, 1, 4, 5]);
        yield from $provider(3, 0, [0, 1]);
        yield from $provider(3, 1, [4, 5]);
        yield from $provider(3, 2, [2, 3]);
    }
}
