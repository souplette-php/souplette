<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Dom\ElementIterator;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Dom\DomBuilder;

final class SelectedTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DomBuilder::create()->tag('select')
            ->tag('option')->attr('selected')->attr('match')->close()
            ->tag('option')->close()
            ->tag('foo')->attr('selected')->close()
            ->getDocument();
        $selector = PseudoClassSelector::of('selected');
        foreach (ElementIterator::descendants($doc) as $element) {
            $mustMatch = $element->hasAttribute('match');
            QueryAssert::elementMatchesSelector($element, $selector, $mustMatch);
        }
    }
}
