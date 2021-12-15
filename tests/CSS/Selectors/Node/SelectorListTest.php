<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node;

use Souplette\CSS\Selectors\Node\Simple\ClassSelector;
use Souplette\CSS\Selectors\Node\Simple\IdSelector;
use Souplette\CSS\Selectors\Node\Simple\TypeSelector;
use Souplette\CSS\Selectors\Specificity;
use Souplette\Tests\CSS\Selectors\SelectorTestCase;
use Souplette\Tests\CSS\Selectors\SelectorUtils;

final class SelectorListTest extends SelectorTestCase
{
    public function toStringProvider(): iterable
    {
        yield [
            SelectorUtils::toSelectorList([
                new TypeSelector('foo', '*'),
                new ClassSelector('bar'),
                new IdSelector('baz'),
            ]),
            'foo, .bar, #baz',
        ];
    }

    public function specificityProvider(): iterable
    {
        yield [
            SelectorUtils::toSelectorList([
                new TypeSelector('foo', '*'),
                new ClassSelector('bar'),
                new IdSelector('baz'),
            ]),
            new Specificity(1, 0, 0),
        ];
    }
}
