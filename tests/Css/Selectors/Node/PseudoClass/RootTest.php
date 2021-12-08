<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Dom\DomBuilder;

final class RootTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DomBuilder::html()->tag('html')
            ->tag('div')->close()
            ->getDocument();
        $selector = PseudoClassSelector::of('root');
        QueryAssert::elementMatchesSelector($doc->documentElement, $selector);
        QueryAssert::elementMatchesSelector($doc->documentElement->firstElementChild, $selector, false);
    }
}
