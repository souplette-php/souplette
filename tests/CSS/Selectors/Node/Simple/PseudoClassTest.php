<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\Simple;

use Souplette\CSS\Selectors\Node\Simple\PseudoClassSelector;
use Souplette\CSS\Selectors\Specificity;
use Souplette\Tests\CSS\Selectors\SelectorTestCase;

final class PseudoClassTest extends SelectorTestCase
{
    public static function toStringProvider(): iterable
    {
        yield [PseudoClassSelector::of('foo'), ':foo'];
    }

    public static function specificityProvider(): iterable
    {
        yield [PseudoClassSelector::of('foo'), new Specificity(0, 1, 0)];
    }
}
