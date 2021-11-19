<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Query\Evaluator\Functional;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Functional\NthChild;
use Souplette\Css\Syntax\Node\AnPlusB;
use Souplette\Tests\Css\Selectors\Query\QueryAssert;
use Souplette\Tests\Html\DomBuilder;

final class NthChildEvaluatorTest extends TestCase
{
    /**
     * @dataProvider simpleAnPlusBProvider
     */
    public function testSimpleAnPlusB(\DOMElement $element, NthChild $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function simpleAnPlusBProvider(): \Generator
    {
        $dom = DomBuilder::create()
            ->tag('a')->close()
            ->tag('a')->close()
            ->tag('a')->close()
            ->getDocument();
        foreach ($dom->childNodes as $i => $node) {
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
    public function testAnPlusB(\DOMElement $element, NthChild $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
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

        $provider = function(int $a, int $b, array $indices) use ($dom) {
            $selector = new NthChild(new AnPlusB($a, $b));
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

        yield from $provider(2, 0, [1, 3, 5]);
        yield from $provider(2, 1, [0, 2, 4]);
        yield from $provider(3, 0, [2, 5]);
        yield from $provider(3, 1, [0, 3]);
        yield from $provider(3, 2, [1, 4]);
    }
}
