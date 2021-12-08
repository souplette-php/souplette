<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Dom\DomBuilder;

final class OnlyOfTypeTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DomBuilder::html()->tag('html')
            ->tag('foo')->close()
            ->tag('bar')->close()
            ->tag('foo')->close()
            ->getDocument();
        $selector = PseudoClassSelector::of('only-of-type');
        $matched = $doc->documentElement->firstElementChild->nextElementSibling;
        QueryAssert::elementMatchesSelector($matched, $selector);

        QueryAssert::elementMatchesSelector($matched->nextElementSibling, $selector, false);
        QueryAssert::elementMatchesSelector($matched->previousElementSibling, $selector, false);
    }

    public function testItMatchesWithoutParentNode()
    {
        $doc = DomBuilder::html()->tag('html')->getDocument();
        $selector = PseudoClassSelector::of('only-of-type');
        QueryAssert::elementMatchesSelector($doc->documentElement, $selector);
    }
}
