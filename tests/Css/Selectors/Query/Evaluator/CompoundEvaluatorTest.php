<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Query\Evaluator;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\CompoundSelector;
use Souplette\Css\Selectors\Node\Selector;
use Souplette\Css\Selectors\Node\Simple\ClassSelector;
use Souplette\Css\Selectors\Node\Simple\TypeSelector;
use Souplette\Tests\Css\Selectors\Query\QueryAssert;
use Souplette\Tests\Dom\DomBuilder;

final class CompoundEvaluatorTest extends TestCase
{
    /**
     * @dataProvider itMatchesClassesProvider
     */
    public function testItMatchesClasses(\DOMElement $element, Selector $selector, bool $expected)
    {
        QueryAssert::elementMatchesSelector($element, $selector, $expected);
    }

    public function itMatchesClassesProvider()
    {
        $dom = DomBuilder::create()
            ->tag('a')->attr('class', 'a')->close()
            ->tag('a')->attr('class', 'a b')->close()
            ->tag('a')->attr('class', 'a b c')->close()
            ->getDocument();

        $provider = function(array $indices, Selector $selector) use ($dom) {
            foreach ($dom->childNodes as $index => $node) {
                $mustMatch = \in_array($index, $indices, true);
                $key = sprintf(
                    "node n°%d %s %s",
                    $index,
                    $mustMatch ? 'matches' : 'does not match',
                    $selector,
                );
                yield $key => [$node, $selector, $mustMatch];
            }
        };

        yield from $provider(
            [0, 1, 2],
            new CompoundSelector([
                new TypeSelector('a', '*'),
                new ClassSelector('a'),
            ]),
        );
        yield from $provider(
            [1, 2],
            new CompoundSelector([
                new TypeSelector('a', '*'),
                new ClassSelector('a'),
                new ClassSelector('b'),
            ]),
        );
        yield from $provider(
            [2],
            new CompoundSelector([
                new ClassSelector('a'),
                new ClassSelector('b'),
                new ClassSelector('c'),
            ]),
        );
        yield from $provider(
            [],
            new CompoundSelector([
                new ClassSelector('a'),
                new ClassSelector('nope'),
            ]),
        );
    }
}