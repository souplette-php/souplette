<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\Functional;

use Souplette\CSS\Selectors\Node\Functional\Where;
use Souplette\CSS\Selectors\Node\Simple\ClassSelector;
use Souplette\CSS\Selectors\Node\Simple\IdSelector;
use Souplette\CSS\Selectors\Node\Simple\TypeSelector;
use Souplette\CSS\Selectors\Specificity;
use Souplette\Tests\CSS\Selectors\SelectorTestCase;
use Souplette\Tests\CSS\Selectors\SelectorUtils;

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
