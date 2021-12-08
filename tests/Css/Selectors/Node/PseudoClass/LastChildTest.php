<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Dom\DomBuilder;

final class LastChildTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DomBuilder::html()->tag('html')
            ->tag('div')->close()
            ->tag('div')->close()
            ->tag('div')->close()
            ->text('Ignore me plz!')
            ->getDocument();
        $selector = PseudoClassSelector::of('last-child');
        for ($i = 0, $node = $doc->documentElement->lastElementChild; $node; $i++, $node = $node->previousElementSibling) {
            QueryAssert::elementMatchesSelector($node, $selector, $i === 0);
        }
    }

    public function testItMatchesWithoutParentNode()
    {
        $doc = DomBuilder::html()->tag('html')->getDocument();
        $selector = PseudoClassSelector::of('last-child');
        QueryAssert::elementMatchesSelector($doc->documentElement, $selector);
    }
}
