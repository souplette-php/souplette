<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Query\Simple;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\ClassSelector;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Tests\Dom\DomBuilder;

final class ClassTest extends TestCase
{
    /**
     * @dataProvider matchesProvider
     */
    public function testItMatches(\DOMElement $element, string $class, bool $caseInsensitive, bool $expected)
    {
        $ctx = QueryContext::of($element);
        $ctx->caseInsensitiveClasses = $caseInsensitive;
        $evaluator = new ClassSelector($class);
        Assert::assertSame($expected, $evaluator->matches($ctx, $element));
    }

    public function matchesProvider(): iterable
    {
        $dom = DomBuilder::create()
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
