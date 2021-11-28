<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Dom\DomBuilder;

final class FirstChildTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DomBuilder::create()->tag('html')
            ->text('Ignore me plz!')
            ->tag('div')->close()
            ->tag('div')->close()
            ->tag('div')->close()
            ->getDocument();
        $selector = PseudoClassSelector::of('first-child');
        for ($i = 0, $node = $doc->documentElement->firstElementChild; $node; $i++, $node = $node->nextElementSibling) {
            QueryAssert::elementMatchesSelector($node, $selector, $i === 0);
        }
    }

    public function testItMatchesWithoutParentNode()
    {
        $doc = DomBuilder::create()->tag('html')->getDocument();
        $selector = PseudoClassSelector::of('first-child');
        QueryAssert::elementMatchesSelector($doc->documentElement, $selector);
    }
}
