<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node;

use Souplette\Css\Selectors\Node\Simple\ClassSelector;
use Souplette\Css\Selectors\Node\Simple\IdSelector;
use Souplette\Css\Selectors\Node\Simple\TypeSelector;
use Souplette\Css\Selectors\Specificity;
use Souplette\Tests\Css\Selectors\SelectorTestCase;
use Souplette\Tests\Css\Selectors\SelectorUtils;

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
