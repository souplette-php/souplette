<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\PseudoClass;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Dom\DomBuilder;

final class EmptyTest extends TestCase
{
    public function testItMatchesElementsWithNoChildren()
    {
        $doc = DomBuilder::html()->tag('html')->getDocument();
        QueryAssert::elementMatchesSelector($doc->documentElement, PseudoClassSelector::of('empty'));
    }

    public function testItMatchesElementsContainingTextOnly()
    {
        $doc = DomBuilder::html()->tag('html')
            ->comment('I do not count')
            ->text(" \n\t")
            ->getDocument();
        QueryAssert::elementMatchesSelector($doc->documentElement, PseudoClassSelector::of('empty'));
    }
}
