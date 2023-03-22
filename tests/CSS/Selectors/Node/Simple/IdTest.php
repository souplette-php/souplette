<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\Simple;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Souplette\CSS\Selectors\Node\Simple\IdSelector;
use Souplette\CSS\Selectors\Query\QueryContext;
use Souplette\CSS\Selectors\Specificity;
use Souplette\DOM\Element;
use Souplette\Tests\CSS\Selectors\SelectorTestCase;
use Souplette\Tests\DOM\DOMBuilder;

final class IdTest extends SelectorTestCase
{
    public static function toStringProvider(): iterable
    {
        yield [new IdSelector('foo'), '#foo'];
    }

    public static function specificityProvider(): iterable
    {
        yield [new IdSelector('foo'), new Specificity(1, 0, 0)];
    }

    #[DataProvider('matchesProvider')]
    public function testItMatches(Element $element, string $id, bool $caseInsensitive, bool $expected)
    {
        $evaluator = new IdSelector($id);
        $ctx = QueryContext::of($element);
        $ctx->caseInsensitiveIds = $caseInsensitive;
        Assert::assertSame($expected, $evaluator->matches($ctx, $element));
    }

    public static function matchesProvider(): iterable
    {
        $dom = DOMBuilder::html()
            ->tag('foo')->id('yep')
            ->getDocument();
        yield 'matches' => [$dom->documentElement, 'yep', false, true];
        yield 'matches case-insensitive' => [$dom->documentElement, 'YEP', true, true];
        yield "doesn't match" => [$dom->documentElement, 'nope', false, false];
        yield "fails case-sensitive" => [$dom->documentElement, 'YEP', false, false];
    }
}
