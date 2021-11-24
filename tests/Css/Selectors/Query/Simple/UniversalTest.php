<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Query\Simple;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\UniversalSelector;
use Souplette\Css\Selectors\Query\QueryContext;
use Souplette\Dom\Namespaces;
use Souplette\Tests\Dom\DomBuilder;

final class UniversalTest extends TestCase
{
    public function testItMatchesAnything()
    {
        $dom = DomBuilder::create()
            ->tag('foo')->close()
            ->tag('bar', Namespaces::SVG)->close()
            ->tag('baz', Namespaces::XML)->close()
            ->getDocument()
        ;
        $evaluator = new UniversalSelector();
        foreach ($dom->children as $child) {
            $ctx = QueryContext::of($child);
            Assert::assertTrue($evaluator->matches($ctx, $child));
        }
    }
}
