<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Query\Evaluator\Simple;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Query\Evaluator\Simple\UniversalEvaluator;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Dom\Namespaces;
use Souplette\Tests\Dom\DomBuilder;

final class UniversalEvaluatorTest extends TestCase
{
    public function testItMatchesAnything()
    {
        $dom = DomBuilder::create()
            ->tag('foo')->close()
            ->tag('bar', Namespaces::SVG)->close()
            ->tag('baz', Namespaces::XML)->close()
            ->getDocument()
        ;
        $evaluator = new UniversalEvaluator();
        foreach ($dom->children as $child) {
            $ctx = QueryContext::of($child);
            Assert::assertTrue($evaluator->matches($ctx, $child));
        }
    }
}
