<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Dom\DomBuilder;

final class FirstOfTypeTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DomBuilder::create()->tag('html')
            ->tag('foo')->close()
            ->tag('bar')->close()
            ->tag('foo')->close()
            ->tag('bar')->close()
            ->getDocument();
        $selector = PseudoClassSelector::of('first-of-type');
        $matched = $doc->documentElement->firstElementChild;
        QueryAssert::elementMatchesSelector($matched, $selector);
        $matched = $matched->nextElementSibling;
        QueryAssert::elementMatchesSelector($matched, $selector);
        //
        $matched = $matched->nextElementSibling;
        QueryAssert::elementMatchesSelector($matched, $selector, false);
        $matched = $matched->nextElementSibling;
        QueryAssert::elementMatchesSelector($matched, $selector, false);
    }
}
