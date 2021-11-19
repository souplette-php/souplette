<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Query\Evaluator\Functional;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Functional\Not;
use Souplette\Css\Selectors\Node\SelectorList;
use Souplette\Css\Selectors\Node\Simple\TypeSelector;
use Souplette\Tests\Css\Selectors\Query\QueryAssert;
use Souplette\Tests\Html\DomBuilder;

final class NotEvaluatorTest extends TestCase
{
    /**
     * @dataProvider matchesProvider
     */
    public function testMatches(\DOMElement $element, Not $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function matchesProvider(): iterable
    {
        $dom = DomBuilder::create()
            ->tag('a')->close()
            ->tag('b')->close()
            ->tag('a')->close()
            ->tag('b')->close()
            ->getDocument();
        foreach ($dom->childNodes as $i => $node) {
            foreach (['a', 'b'] as $tagName) {
                $mustMatch = $node->tagName !== $tagName;
                $selector = new Not(new SelectorList([
                    new TypeSelector($tagName, '*'),
                ]));
                $key = sprintf(
                    'child n°%d %s selector %s',
                    $i,
                    $mustMatch ? 'matches' : 'does not match',
                    $selector,
                );
                yield $key => [
                    $node,
                    $selector,
                    $mustMatch,
                ];
            }
        }
    }
}
