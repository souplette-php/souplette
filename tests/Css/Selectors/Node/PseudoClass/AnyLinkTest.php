<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Dom\DomBuilder;

final class AnyLinkTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DomBuilder::create()->tag('html')
            ->tag('a')->attr('href', '#foo')->close()
            ->tag('a')->close()
            ->tag('area')->attr('href', '#foo')
            ->tag('area')
            ->tag('link')->attr('href', '#foo')
            ->getDocument();
        $selector = PseudoClassSelector::of('any-link');

        $matched = $doc->documentElement->firstElementChild;
        QueryAssert::elementMatchesSelector($matched, $selector);
        $matched = $matched->nextElementSibling;
        QueryAssert::elementMatchesSelector($matched, $selector, false);
        $matched = $matched->nextElementSibling;
        QueryAssert::elementMatchesSelector($matched, $selector);
        $matched = $matched->nextElementSibling;
        QueryAssert::elementMatchesSelector($matched, $selector, false);
        $matched = $matched->nextElementSibling;
        QueryAssert::elementMatchesSelector($matched, $selector, false);
    }
}
