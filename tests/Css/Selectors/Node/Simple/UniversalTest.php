<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\Simple;

use Souplette\Css\Selectors\Node\Simple\UniversalSelector;
use Souplette\Css\Selectors\Specificity;
use Souplette\Dom\Namespaces;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Css\Selectors\SelectorTestCase;
use Souplette\Tests\Dom\DomBuilder;

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
        $dom = DomBuilder::create()
            ->tag('foo')->close()
            ->tag('bar', Namespaces::SVG)->close()
            ->tag('baz', Namespaces::XML)->close()
            ->getDocument()
        ;
        $selector = new UniversalSelector();
        foreach ($dom->childNodes as $child) {
            QueryAssert::elementMatchesSelector($child, $selector);
        }
    }
}
