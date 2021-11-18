<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Query\Evaluator\Functional;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Functional\NthOfType;
use Souplette\Css\Selectors\Query\Compiler;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Css\Syntax\Node\AnPlusB;
use Souplette\Tests\Html\DomBuilder;

final class NthOfTypeEvaluatorTest extends TestCase
{
    private static function assertMatches(\DOMElement $element, NthOfType $selector, bool $expected)
    {
        $ctx = QueryContext::of($element);
        $evaluator = (new Compiler)->compile($selector);
        Assert::assertSame($expected, $evaluator->matches($ctx, $element));
    }

    /**
     * @dataProvider simpleAnPlusBProvider
     */
    public function testSimpleAnPlusB(\DOMElement $element, NthOfType $selector, bool $expected)
    {
        self::assertMatches($element, $selector, $expected);
    }

    public function simpleAnPlusBProvider(): \Generator
    {
        $dom = DomBuilder::create()
            ->tag('a')->close()
            ->tag('b')->close()
            ->tag('a')->close()
            ->tag('b')->close()
            ->tag('a')->close()
            ->tag('b')->close()
            ->getDocument();
        $indices = [];
        foreach ($dom->childNodes as $node) {
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

    /**
     * @dataProvider aNPlusBProvider
     */
    public function testAnPlusB(\DOMElement $element, NthOfType $selector, bool $expected)
    {
        self::assertMatches($element, $selector, $expected);
    }

    public function aNPlusBProvider(): \Generator
    {
        $dom = DomBuilder::create()
            ->tag('a')->close()
            ->tag('b')->close()
            ->tag('a')->close()
            ->tag('b')->close()
            ->tag('a')->close()
            ->tag('b')->close()
            ->getDocument();

        $provider = static function(int $a, int $b, array $indices) use ($dom) {
            $selector = new NthOfType(new AnPlusB($a, $b));
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

        yield from $provider(2, 0, [2, 3]);
        yield from $provider(2, 1, [0, 1, 4, 5]);
        yield from $provider(3, 0, [4, 5]);
        yield from $provider(3, 1, [0, 1]);
        yield from $provider(3, 2, [2, 3]);
    }
}
