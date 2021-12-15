<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node;

use Souplette\CSS\Selectors\Node\FunctionalSelector;
use Souplette\CSS\Selectors\Specificity;
use Souplette\Tests\CSS\Selectors\SelectorTestCase;

final class FunctionalSelectorTest extends SelectorTestCase
{
    public function toStringProvider(): iterable
    {
        yield [new FunctionalSelector('foo', ['bar', 'baz']), ':foo(bar, baz)'];
    }

    public function specificityProvider(): iterable
    {
        yield [new FunctionalSelector('foo', ['bar', 'baz']), new Specificity(0, 1, 0)];
    }
}
