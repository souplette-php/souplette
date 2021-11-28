<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\Simple;

use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Simple\PseudoElementSelector;
use Souplette\Css\Selectors\Specificity;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Css\Selectors\SelectorTestCase;
use Souplette\Tests\Dom\DomBuilder;

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
        $doc = DomBuilder::create()->tag('html')->getDocument();
        $selector = new PseudoElementSelector('before');
        QueryAssert::elementMatchesSelector($doc->documentElement, $selector, false);
    }
}
