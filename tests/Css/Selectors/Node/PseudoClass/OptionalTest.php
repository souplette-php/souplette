<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Dom\Traversal\ElementTraversal;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Dom\DomBuilder;

final class OptionalTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DomBuilder::html()->tag('form')
            ->tag('input')->attr('required')
            ->tag('input')->attr('match')
            ->tag('select')->attr('required')
            ->tag('select')->attr('match')
            ->tag('textarea')->attr('required')
            ->tag('textarea')->attr('match')
            ->tag('foo')->attr('required')
            ->getDocument();
        $selector = PseudoClassSelector::of('optional');
        foreach (ElementTraversal::descendantsOf($doc) as $element) {
            $mustMatch = $element->hasAttribute('match');
            QueryAssert::elementMatchesSelector($element, $selector, $mustMatch);
        }
    }
}
