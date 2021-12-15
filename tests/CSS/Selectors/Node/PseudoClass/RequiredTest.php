<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\DOM\Traversal\ElementTraversal;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\DOM\DOMBuilder;

final class RequiredTest extends TestCase
{
    public function testItMatches()
    {
        $doc = DOMBuilder::html()->tag('form')
            ->tag('input')->attr('required')->attr('match')
            ->tag('input')
            ->tag('select')->attr('required')->attr('match')
            ->tag('select')
            ->tag('textarea')->attr('required')->attr('match')
            ->tag('textarea')
            ->tag('foo')->attr('required')
            ->getDocument();
        $selector = PseudoClassSelector::of('required');
        foreach (ElementTraversal::descendantsOf($doc) as $element) {
            $mustMatch = $element->hasAttribute('match');
            QueryAssert::elementMatchesSelector($element, $selector, $mustMatch);
        }
    }
}
