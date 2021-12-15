<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\DOM\Traversal\ElementTraversal;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\DOM\DOMBuilder;

final class DefaultTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DOMBuilder::html()->tag('html')
            ->tag('input')
                ->attr('type', 'checkbox')
                ->attr('checked')
                ->attr('match')
            ->tag('input')
                ->attr('type', 'radio')
                ->attr('checked')
                ->attr('match')
            ->tag('option')->attr('selected')->attr('match')->close()
            ->tag('input')->attr('checked')
            ->tag('option')->close()
            ->tag('div')->attr('checked')->close()
            ->getDocument();

        $selector = PseudoClassSelector::of('default');
        foreach (ElementTraversal::descendantsOf($doc) as $element) {
            $mustMatch = $element->hasAttribute('match');
            QueryAssert::elementMatchesSelector($element, $selector, $mustMatch);
        }
    }
}
