<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors\Node\Functional;

use Souplette\CSS\Selectors\Node\Functional\Is;
use Souplette\CSS\Selectors\Node\Simple\ClassSelector;
use Souplette\CSS\Selectors\Node\Simple\IdSelector;
use Souplette\CSS\Selectors\Node\Simple\TypeSelector;
use Souplette\CSS\Selectors\Specificity;
use Souplette\Tests\CSS\Selectors\SelectorTestCase;
use Souplette\Tests\CSS\Selectors\SelectorUtils;

final class IsTest extends SelectorTestCase
{
    public static function toStringProvider(): iterable
    {
        yield [
            new Is(SelectorUtils::toSelectorList([
                new TypeSelector('foo', '*'),
                new TypeSelector('bar', '*'),
            ])),
            ':is(foo, bar)',
        ];
    }

    public static function specificityProvider(): iterable
    {
        yield [
            new Is(SelectorUtils::toSelectorList([
                new TypeSelector('foo', '*'),
                new ClassSelector('bar'),
                new IdSelector('baz'),
            ])),
            new Specificity(1, 0, 0),
        ];
    }
}
