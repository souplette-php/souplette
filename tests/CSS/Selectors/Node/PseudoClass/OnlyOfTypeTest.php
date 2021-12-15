<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\DOM\DOMBuilder;

final class OnlyOfTypeTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DOMBuilder::html()->tag('html')
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
        $doc = DOMBuilder::html()->tag('html')->getDocument();
        $selector = PseudoClassSelector::of('only-of-type');
        QueryAssert::elementMatchesSelector($doc->documentElement, $selector);
    }
}
