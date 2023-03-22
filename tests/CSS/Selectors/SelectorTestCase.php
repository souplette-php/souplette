<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Selectors;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Souplette\CSS\Selectors\Node\Selector;
use Souplette\CSS\Selectors\Specificity;

abstract class SelectorTestCase extends TestCase
{
    #[DataProvider('toStringProvider')]
    public function testToString(Selector $selector, string $expected)
    {
        Assert::assertSame($expected, (string)$selector);
    }

    abstract public static function toStringProvider(): iterable;

    #[DataProvider('specificityProvider')]
    public function testSpecificity(Selector $selector, Specificity $expected)
    {
        SelectorAssert::specificityEquals($selector, $expected);
    }

    abstract public static function specificityProvider(): iterable;
}
