<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors\Node\Functional;

use Souplette\Css\Selectors\Node\Functional\Where;
use Souplette\Css\Selectors\Node\Simple\ClassSelector;
use Souplette\Css\Selectors\Node\Simple\IdSelector;
use Souplette\Css\Selectors\Node\Simple\TypeSelector;
use Souplette\Css\Selectors\Specificity;
use Souplette\Tests\Css\Selectors\SelectorTestCase;
use Souplette\Tests\Css\Selectors\SelectorUtils;

final class WhereTest extends SelectorTestCase
{
    public function toStringProvider(): iterable
    {
        yield [
            new Where(SelectorUtils::toSelectorList([
                new TypeSelector('foo', '*'),
                new TypeSelector('bar', '*'),
            ])),
            ':where(foo, bar)',
        ];
    }

    public function specificityProvider(): iterable
    {
        yield [
            new Where(SelectorUtils::toSelectorList([
                new TypeSelector('foo', '*'),
                new ClassSelector('bar'),
                new IdSelector('baz'),
            ])),
            new Specificity(0, 0, 0),
        ];
    }
}
