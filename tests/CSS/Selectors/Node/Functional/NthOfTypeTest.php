<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Souplette\CSS\Selectors\Node\Functional\NthOfType;
use Souplette\CSS\Selectors\Specificity;
use Souplette\CSS\Syntax\Node\AnPlusB;
use Souplette\DOM\Element;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\CSS\Selectors\SelectorTestCase;
use Souplette\Tests\DOM\DOMBuilder;

final class NthOfTypeTest extends SelectorTestCase
{
    public static function toStringProvider(): iterable
    {
        yield [new NthOfType(new AnPlusB(2, 1)), ':nth-of-type(odd)'];
    }

    public static function specificityProvider(): iterable
    {
        yield [new NthOfType(new AnPlusB(2, 1)), new Specificity(0, 1, 0)];
    }

    #[DataProvider('simpleAnPlusBProvider')]
    public function testSimpleAnPlusB(Element $element, NthOfType $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public static function simpleAnPlusBProvider(): iterable
    {
        $dom = DOMBuilder::html()->tag('html')
            ->tag('a')->close()
            ->tag('b')->close()
            ->tag('a')->close()
            ->tag('b')->close()
            ->tag('a')->close()
            ->tag('b')->close()
            ->getDocument();
        $indices = [];
        foreach ($dom->documentElement->children as $node) {
            $indices[$node->localName] ??= 1;
            $b = $indices[$node->localName]++;
            $key = sprintf('matches %s:nth-of-type(%d)', $node->localName, $b);
            yield $key => [
                $node,
                new NthOfType(new AnPlusB(0, $b)),
                true,
            ];
        }
    }

    #[DataProvider('aNPlusBProvider')]
    public function testAnPlusB(Element $element, NthOfType $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public static function aNPlusBProvider(): iterable
    {
        $dom = DOMBuilder::html()->tag('html')
            ->tag('a')->close()
            ->tag('b')->close()
            ->tag('a')->close()
            ->tag('b')->close()
            ->tag('a')->close()
            ->tag('b')->close()
            ->getDocument();

        $provider = static function(int $a, int $b, array $indices) use ($dom) {
            $selector = new NthOfType(new AnPlusB($a, $b));
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
        yield from $provider(3, 0, [4, 5]);
        yield from $provider(3, 1, [0, 1]);
        yield from $provider(3, 2, [2, 3]);
    }
}
