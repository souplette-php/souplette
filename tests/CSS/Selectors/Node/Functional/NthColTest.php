<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\Functional;

use Souplette\CSS\Selectors\Node\Functional\NthCol;
use Souplette\CSS\Selectors\Specificity;
use Souplette\CSS\Syntax\Node\AnPlusB;
use Souplette\Tests\CSS\Selectors\QueryAssert;
use Souplette\Tests\CSS\Selectors\SelectorTestCase;
use Souplette\Tests\DOM\DOMBuilder;

final class NthColTest extends SelectorTestCase
{
    public function toStringProvider(): iterable
    {
        yield [new NthCol(new AnPlusB(2, 1)), ':nth-col(odd)'];
    }

    public function specificityProvider(): iterable
    {
        yield [new NthCol(new AnPlusB(2, 1)), new Specificity(0, 1, 0)];
    }

    public function testItNeverMatches()
    {
        $doc = DOMBuilder::html()->tag('html')->getDocument();
        $selector = new NthCol(new AnPlusB(1, 0));
        QueryAssert::elementMatchesSelector($doc->documentElement, $selector, false);
    }
}
