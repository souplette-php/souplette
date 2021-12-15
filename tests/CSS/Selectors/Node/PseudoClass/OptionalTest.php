<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\DOM\Traversal\ElementTraversal;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\DOM\DOMBuilder;

final class OptionalTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DOMBuilder::html()->tag('form')
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
