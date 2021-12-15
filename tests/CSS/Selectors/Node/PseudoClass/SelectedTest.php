<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\DOM\Traversal\ElementTraversal;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\DOM\DOMBuilder;

final class SelectedTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DOMBuilder::html()->tag('select')
            ->tag('option')->attr('selected')->attr('match')->close()
            ->tag('option')->close()
            ->tag('foo')->attr('selected')->close()
            ->getDocument();
        $selector = PseudoClassSelector::of('selected');
        foreach (ElementTraversal::descendantsOf($doc) as $element) {
            $mustMatch = $element->hasAttribute('match');
            QueryAssert::elementMatchesSelector($element, $selector, $mustMatch);
        }
    }
}
