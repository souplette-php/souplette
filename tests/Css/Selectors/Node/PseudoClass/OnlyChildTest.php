<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Dom\DomBuilder;

final class OnlyChildTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DomBuilder::create()->tag('html')
            ->text('Ignore me plz!')
            ->tag('div')->close()
            ->comment('Ignore me plz!')
            ->getDocument();
        $selector = PseudoClassSelector::of('only-child');
        QueryAssert::elementMatchesSelector($doc->documentElement->firstElementChild, $selector);
    }

    public function testItDoesntMatch()
    {
        $doc = DomBuilder::create()->tag('html')
            ->tag('foo')->close()
            ->tag('bar')->close()
            ->getDocument();
        $selector = PseudoClassSelector::of('only-child');
        QueryAssert::elementMatchesSelector($doc->documentElement->firstElementChild, $selector, false);
    }

    public function testItMatchesWithoutParentNode()
    {
        $doc = DomBuilder::create()->tag('html')->getDocument();
        $selector = PseudoClassSelector::of('only-child');
        QueryAssert::elementMatchesSelector($doc->documentElement, $selector);
    }
}
