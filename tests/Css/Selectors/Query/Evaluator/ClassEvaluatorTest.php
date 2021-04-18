<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Query\Evaluator;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\ClassSelector;
use Souplette\Css\Selectors\Query\Evaluator\ClassEvaluator;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Tests\Html\DomBuilder;

final class ClassEvaluatorTest extends TestCase
{
    /**
     * @dataProvider matchesProvider
     */
    public function testItMatches(\DOMElement $element, string $class, bool $caseInsensitive, bool $expected)
    {
        $ctx = new QueryContext($element);
        $ctx->selector = new ClassSelector($class);
        $ctx->caseInsensitiveClasses = $caseInsensitive;
        $evaluator = new ClassEvaluator();
        Assert::assertSame($expected, $evaluator->matches($ctx));
    }

    public function matchesProvider(): \Generator
    {
        $dom = DomBuilder::create()
            ->tag('foo')->attr('class', 'foo bar baz qux')
            ->getDocument();

        yield 'matches' => [$dom->documentElement, 'bar', false, true];
        yield "doesn't match" => [$dom->documentElement, 'nope', false, false];
        yield 'matches case-insensitive' => [$dom->documentElement, 'BAR', true, true];
        yield 'fails case-sensitive' => [$dom->documentElement, 'BAR', false, false];
        yield 'matches at start' => [$dom->documentElement, 'foo', false, true];
        yield 'matches at end' => [$dom->documentElement, 'qux', false, true];
    }
}
