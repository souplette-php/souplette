<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\Simple;

use Souplette\CSS\Selectors\Node\Simple\PseudoElementSelector;
use Souplette\CSS\Selectors\Specificity;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\CSS\Selectors\SelectorTestCase;
use Souplette\Tests\DOM\DOMBuilder;

final class PseudoElementTest extends SelectorTestCase
{
    public function toStringProvider(): iterable
    {
        yield [new PseudoElementSelector('before'), '::before'];
    }

    public function specificityProvider(): iterable
    {
        yield [new PseudoElementSelector('before'), new Specificity(0, 0, 1)];
    }

    public function testPseudoElementsAreNotSupported()
    {
        $doc = DOMBuilder::html()->tag('html')->getDocument();
        $selector = new PseudoElementSelector('before');
        QueryAssert::elementMatchesSelector($doc->documentElement, $selector, false);
    }
}
