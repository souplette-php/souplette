<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Dom\Traversal\ElementTraversal;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Dom\DomBuilder;

final class SelectedTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DomBuilder::html()->tag('select')
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
