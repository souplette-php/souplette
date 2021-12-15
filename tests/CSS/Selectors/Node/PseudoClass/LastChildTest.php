<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\DOM\DOMBuilder;

final class LastChildTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DOMBuilder::html()->tag('html')
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
        $doc = DOMBuilder::html()->tag('html')->getDocument();
        $selector = PseudoClassSelector::of('last-child');
        QueryAssert::elementMatchesSelector($doc->documentElement, $selector);
    }
}
