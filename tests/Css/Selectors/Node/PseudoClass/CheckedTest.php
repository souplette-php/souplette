<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Dom\ElementIterator;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Dom\DomBuilder;

final class CheckedTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DomBuilder::create()->tag('html')
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

        $selector = PseudoClassSelector::of('checked');
        foreach (ElementIterator::descendants($doc) as $element) {
            $mustMatch = $element->hasAttribute('match');
            QueryAssert::elementMatchesSelector($element, $selector, $mustMatch);
        }
    }
}
