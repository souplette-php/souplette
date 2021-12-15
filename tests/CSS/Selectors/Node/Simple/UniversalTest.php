<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\Simple;

use Souplette\CSS\Selectors\Node\Simple\UniversalSelector;
use Souplette\CSS\Selectors\Specificity;
use Souplette\DOM\Namespaces;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\CSS\Selectors\SelectorTestCase;
use Souplette\Tests\DOM\DOMBuilder;

final class UniversalTest extends SelectorTestCase
{
    public function toStringProvider(): iterable
    {
        yield [new UniversalSelector('*'), '*'];
        yield [new UniversalSelector(null), '|*'];
        yield [new UniversalSelector('svg'), 'svg|*'];
    }

    public function specificityProvider(): iterable
    {
        yield [new UniversalSelector(), new Specificity(0, 0, 0)];
    }

    public function testItMatchesAnything()
    {
        $dom = DOMBuilder::html()->tag('html')
            ->tag('foo')->close()
            ->tag('bar', Namespaces::SVG)->close()
            ->tag('baz', Namespaces::XML)->close()
            ->getDocument()
        ;
        $selector = new UniversalSelector();
        foreach ($dom->documentElement->children as $child) {
            QueryAssert::elementMatchesSelector($child, $selector);
        }
    }
}
