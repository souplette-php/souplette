<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Node\Functional\NthCol;
use Souplette\Css\Selectors\Specificity;
use Souplette\Css\Syntax\Node\AnPlusB;
use Souplette\Tests\Css\Selectors\QueryAssert;
use Souplette\Tests\Css\Selectors\SelectorTestCase;
use Souplette\Tests\Dom\DomBuilder;

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
        $doc = DomBuilder::create()->tag('html')->getDocument();
        $selector = new NthCol(new AnPlusB(1, 0));
        QueryAssert::elementMatchesSelector($doc->documentElement, $selector, false);
    }
}
