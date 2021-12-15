<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\DOM\DOMBuilder;

final class LastOfTypeTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DOMBuilder::html()->tag('html')
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
