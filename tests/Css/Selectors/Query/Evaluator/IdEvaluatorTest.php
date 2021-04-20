<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Query\Evaluator;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\IdSelector;
use Souplette\Css\Selectors\Query\Evaluator\Simple\IdEvaluator;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Tests\Html\DomBuilder;

final class IdEvaluatorTest extends TestCase
{
    /**
     * @dataProvider matchesProvider
     */
    public function testItMatches(\DOMElement $element, string $id, bool $caseInsensitive, bool $expected)
    {
        $evaluator = new IdEvaluator();
        $ctx = new QueryContext($element);
        $ctx->selector = new IdSelector($id);
        $ctx->caseInsensitiveIds = $caseInsensitive;
        Assert::assertSame($expected, $evaluator->matches($ctx));
    }

    public function matchesProvider(): \Generator
    {
        $dom = DomBuilder::create()
            ->tag('foo')->attr('id', 'yep')
            ->getDocument();
        yield 'matches' => [$dom->documentElement, 'yep', false, true];
        yield 'matches case-insensitive' => [$dom->documentElement, 'YEP', true, true];
        yield "doesn't match" => [$dom->documentElement, 'nope', false, false];
        yield "fails case-sensitive" => [$dom->documentElement, 'YEP', false, false];
    }
}
