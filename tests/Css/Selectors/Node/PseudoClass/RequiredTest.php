<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Dom\ElementIterator;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Dom\DomBuilder;

final class RequiredTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DomBuilder::create()->tag('form')
            ->tag('input')->attr('required')->attr('match')
            ->tag('input')
            ->tag('select')->attr('required')->attr('match')
            ->tag('select')
            ->tag('textarea')->attr('required')->attr('match')
            ->tag('textarea')
            ->tag('foo')->attr('required')
            ->getDocument();
        $selector = PseudoClassSelector::of('required');
        foreach (ElementIterator::descendants($doc) as $element) {
            $mustMatch = $element->hasAttribute('match');
            QueryAssert::elementMatchesSelector($element, $selector, $mustMatch);
        }
    }
}
