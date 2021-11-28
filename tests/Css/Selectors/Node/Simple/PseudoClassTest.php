<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\Simple;

use Souplette\Css\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\Css\Selectors\Specificity;
use Souplette\Tests\Css\Selectors\SelectorTestCase;

final class PseudoClassTest extends SelectorTestCase
{
    public function toStringProvider(): iterable
    {
        yield [PseudoClassSelector::of('foo'), ':foo'];
    }

    public function specificityProvider(): iterable
    {
        yield [PseudoClassSelector::of('foo'), new Specificity(0, 1, 0)];
    }
}
