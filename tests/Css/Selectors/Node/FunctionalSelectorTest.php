<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node;

use Souplette\Css\Selectors\Node\FunctionalSelector;
use Souplette\Css\Selectors\Specificity;
use Souplette\Tests\Css\Selectors\SelectorTestCase;

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
