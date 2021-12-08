<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Dom\DomBuilder;

final class LastOfTypeTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DomBuilder::html()->tag('html')
            ->tag('foo')->close()
            ->tag('bar')->close()
            ->tag('foo')->close()
            ->tag('bar')->close()
            ->getDocument();
        $selector = PseudoClassSelector::of('last-of-type');
        $matched = $doc->documentElement->lastElementChild;
        QueryAssert::elementMatchesSelector($matched, $selector);
        $matched = $matched->previousElementSibling;
        QueryAssert::elementMatchesSelector($matched, $selector);
        //
        $matched = $matched->previousElementSibling;
        QueryAssert::elementMatchesSelector($matched, $selector, false);
        $matched = $matched->previousElementSibling;
        QueryAssert::elementMatchesSelector($matched, $selector, false);
    }
}
