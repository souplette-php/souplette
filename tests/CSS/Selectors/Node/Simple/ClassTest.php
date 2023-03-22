<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\Simple;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Souplette\CSS\Selectors\Node\Simple\ClassSelector;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\CSS\Selectors\Specificity;
use Souplette\DOM\Element;
use Souplette\Tests\CSS\Selectors\SelectorTestCase;
use Souplette\Tests\DOM\DOMBuilder;

final class ClassTest extends SelectorTestCase
{
    public static function toStringProvider(): iterable
    {
        yield [new ClassSelector('foo'), '.foo'];
    }

    public static function specificityProvider(): iterable
    {
        yield [new ClassSelector('foo'), new Specificity(0, 1, 0)];
    }

    #[DataProvider('matchesProvider')]
    public function testItMatches(Element $element, string $class, bool $caseInsensitive, bool $expected)
    {
        $ctx = QueryContext::of($element);
        $ctx->caseInsensitiveClasses = $caseInsensitive;
        $evaluator = new ClassSelector($class);
        Assert::assertSame($expected, $evaluator->matches($ctx, $element));
    }

    public static function matchesProvider(): iterable
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
