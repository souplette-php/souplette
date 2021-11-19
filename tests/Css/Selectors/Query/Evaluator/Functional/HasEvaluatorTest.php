<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Query\Evaluator\Functional;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Functional\Has;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Node\Simple\TypeSelector;
use Souplette\Css\Selectors\Query\Compiler;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Tests\Html\DomBuilder;

final class HasEvaluatorTest extends TestCase
{
    private static function assertMatches(\DOMElement $element, Has $selector, bool $expected)
    {
        $ctx = QueryContext::of($element);
        $evaluator = (new Compiler)->compile($selector);
        Assert::assertSame($expected, $evaluator->matches($ctx, $element));
    }

    /**
     * @dataProvider matchesProvider
     */
    public function testMatches(\DOMElement $element, Has $selector, bool $expected)
    {
        self::assertMatches($element, $selector, $expected);
    }

    public function matchesProvider(): iterable
    {
        $dom = DomBuilder::create()
            ->tag('a')
                ->tag('b')->close()
            ->close()
            ->tag('a')
                ->tag('c')->close()
            ->close()
            ->tag('a')
                ->tag('d')->close()
            ->close()
            ->tag('a')
                ->tag('foo')
                    ->tag('e')->close()
                ->close()
            ->close()
            ->getDocument();
        foreach (['b', 'c', 'd', 'e'] as $i => $tagName) {
            $selector = new Has(new SelectorList([new TypeSelector($tagName, '*')]));
            yield "node {$i} matches {$selector}" => [
                $dom->childNodes->item($i),
                $selector,
                true,
            ];
        }
        yield "node 0 matches :has(a, b)" => [
            $dom->childNodes->item(0),
            new Has(new SelectorList([
                new TypeSelector('a', '*'),
                new TypeSelector('b', '*'),
            ])),
            true,
        ];
        yield "node 1 does not matches :has(a, b)" => [
            $dom->childNodes->item(1),
            new Has(new SelectorList([
                new TypeSelector('a', '*'),
                new TypeSelector('b', '*'),
            ])),
            false,
        ];
    }
}
