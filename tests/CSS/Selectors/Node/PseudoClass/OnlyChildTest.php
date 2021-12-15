<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\DOM\DOMBuilder;

final class OnlyChildTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DOMBuilder::html()->tag('html')
            ->text('Ignore me plz!')
            ->tag('div')->close()
            ->comment('Ignore me plz!')
            ->getDocument();
        $selector = PseudoClassSelector::of('only-child');
        QueryAssert::elementMatchesSelector($doc->documentElement->firstElementChild, $selector);
    }

    public function testItDoesntMatch()
    {
        $doc = DOMBuilder::html()->tag('html')
            ->tag('foo')->close()
            ->tag('bar')->close()
            ->getDocument();
        $selector = PseudoClassSelector::of('only-child');
        QueryAssert::elementMatchesSelector($doc->documentElement->firstElementChild, $selector, false);
    }

    public function testItMatchesWithoutParentNode()
    {
        $doc = DOMBuilder::html()->tag('html')->getDocument();
        $selector = PseudoClassSelector::of('only-child');
        QueryAssert::elementMatchesSelector($doc->documentElement, $selector);
    }
}
