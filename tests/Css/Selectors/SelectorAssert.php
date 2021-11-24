<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors;

use PHPUnit\Framework\Assert;
use Souplette\Css\Selectors\Node\ComplexSelector;
use Souplette\Css\Selectors\Node\SelectorList;

final class SelectorAssert
{
    public static function selectorEquals(ComplexSelector $expected, ComplexSelector $actual)
    {
        Assert::assertEquals($expected, $actual);
    }

    public static function selectorListEquals(SelectorList $expected, SelectorList $actual)
    {
        Assert::assertSame(
            \count($expected),
            \count($actual),
        );
        foreach ($expected->selectors as $i => $expectedSelector) {
            $actualSelector = $actual->selectors[$i];
            self::selectorEquals($expectedSelector, $actualSelector);
        }
    }
}
