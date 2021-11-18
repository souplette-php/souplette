<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Query\Evaluator\Functional;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Functional\NthLastChild;
use Souplette\Css\Selectors\Query\Compiler;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Syntax\Node\AnPlusB;
use Souplette\Tests\Html\DomBuilder;

final class NthLastChildEvaluatorTest extends TestCase
{
    private static function assertMatches(\DOMElement $element, NthLastChild $selector, bool $expected)
    {
        $ctx = QueryContext::of($element);
        $evaluator = (new Compiler)->compile($selector);
        Assert::assertSame($expected, $evaluator->matches($ctx, $element));
    }

    /**
     * @dataProvider simpleAnPlusBProvider
     */
    public function testSimpleAnPlusB(\DOMElement $element, NthLastChild $selector, bool $expected)
    {
        self::assertMatches($element, $selector, $expected);
    }

    public function simpleAnPlusBProvider(): \Generator
    {
        $dom = DomBuilder::create()
            ->tag('a')->close()
            ->tag('a')->close()
            ->tag('a')->close()
            ->getDocument();
        $nodes = iterator_to_array($dom->childNodes);
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
    public function testAnPlusB(\DOMElement $element, NthLastChild $selector, bool $expected)
    {
        self::assertMatches($element, $selector, $expected);
    }

    public function aNPlusBProvider(): \Generator
    {
        $dom = DomBuilder::create()
            ->tag('a')->close()
            ->tag('a')->close()
            ->tag('a')->close()
            ->tag('a')->close()
            ->tag('a')->close()
            ->tag('a')->close()
            ->getDocument();

        $provider = static function(int $a, int $b, array $indices) use($dom) {
            $selector = new NthLastChild(new AnPlusB($a, $b));
            foreach ($dom->childNodes as $index => $node) {
                $mustMatch = in_array($index, $indices, true);
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
