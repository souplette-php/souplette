<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\DOM\DOMBuilder;

final class EmptyTest extends TestCase
{
    public function testItMatchesElementsWithNoChildren()
    {
        $doc = DOMBuilder::html()->tag('html')->getDocument();
        QueryAssert::elementMatchesSelector($doc->documentElement, PseudoClassSelector::of('empty'));
    }

    public function testItMatchesElementsContainingTextOnly()
    {
        $doc = DOMBuilder::html()->tag('html')
            ->comment('I do not count')
            ->text(" \n\t")
            ->getDocument();
        QueryAssert::elementMatchesSelector($doc->documentElement, PseudoClassSelector::of('empty'));
    }
}
