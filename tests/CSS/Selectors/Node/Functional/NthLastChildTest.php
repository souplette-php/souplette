<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Souplette\CSS\Selectors\Node\Functional\NthLastChild;
use Souplette\CSS\Selectors\Node\Simple\ClassSelector;
use Souplette\CSS\Selectors\Node\Simple\IdSelector;
use Souplette\CSS\Selectors\Node\Simple\TypeSelector;
use Souplette\CSS\Selectors\Specificity;
use Souplette\CSS\Syntax\Node\AnPlusB;
use Souplette\DOM\Element;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\CSS\Selectors\SelectorTestCase;
use Souplette\Tests\CSS\Selectors\SelectorUtils;
use Souplette\Tests\DOM\DOMBuilder;

final class NthLastChildTest extends SelectorTestCase
{
    public static function toStringProvider(): iterable
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

    public static function specificityProvider(): iterable
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

    #[DataProvider('simpleAnPlusBProvider')]
    public function testSimpleAnPlusB(Element $element, NthLastChild $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public static function simpleAnPlusBProvider(): iterable
    {
        $dom = DOMBuilder::html()->tag('html')
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

    #[DataProvider('aNPlusBProvider')]
    public function testAnPlusB(Element $element, NthLastChild $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public static function aNPlusBProvider(): iterable
    {
        $dom = DOMBuilder::html()->tag('html')
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
