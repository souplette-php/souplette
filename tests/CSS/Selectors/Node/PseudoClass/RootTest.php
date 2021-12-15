<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\DOM\DOMBuilder;

final class RootTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DOMBuilder::html()->tag('html')
            ->tag('div')->close()
            ->getDocument();
        $selector = PseudoClassSelector::of('root');
        QueryAssert::elementMatchesSelector($doc->documentElement, $selector);
        QueryAssert::elementMatchesSelector($doc->documentElement->firstElementChild, $selector, false);
    }
}
