<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\Simple;

use Souplette\DOM\Element;
use PHPUnit\Framework\Assert;
use Souplette\CSS\Selectors\Node\Simple\ClassSelector;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\CSS\Selectors\Specificity;
use Souplette\Tests\CSS\Selectors\SelectorTestCase;
use Souplette\Tests\DOM\DOMBuilder;

final class ClassTest extends SelectorTestCase
{
    public function toStringProvider(): iterable
    {
        yield [new ClassSelector('foo'), '.foo'];
    }

    public function specificityProvider(): iterable
    {
        yield [new ClassSelector('foo'), new Specificity(0, 1, 0)];
    }

    /**
     * @dataProvider matchesProvider
     */
    public function testItMatches(Element $element, string $class, bool $caseInsensitive, bool $expected)
    {
        $ctx = QueryContext::of($element);
        $ctx->caseInsensitiveClasses = $caseInsensitive;
        $evaluator = new ClassSelector($class);
        Assert::assertSame($expected, $evaluator->matches($ctx, $element));
    }

    public function matchesProvider(): iterable
    {
        $dom = DOMBuilder::html()
            ->tag('foo')->class('foo bar baz qux')
            ->getDocument();

        yield 'matches' => [$dom->documentElement, 'bar', false, true];
        yield "doesn't match" => [$dom->documentElement, 'nope', false, false];
        yield 'matches case-insensitive' => [$dom->documentElement, 'BAR', true, true];
        yield 'fails case-sensitive' => [$dom->documentElement, 'BAR', false, false];
        yield 'matches at start' => [$dom->documentElement, 'foo', false, true];
        yield 'matches at end' => [$dom->documentElement, 'qux', false, true];
    }
}
