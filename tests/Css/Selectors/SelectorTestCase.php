<?php declare(strict_types=1);

namespace Souplette\Tests\Css\Selectors;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\Css\Selectors\Node\Selector;
use Souplette\Css\Selectors\Specificity;

abstract class SelectorTestCase extends TestCase
{
    /**
     * @dataProvider toStringProvider
     */
    public function testToString(Selector $selector, string $expected)
    {
        Assert::assertSame($expected, (string)$selector);
    }

    abstract public function toStringProvider(): iterable;

    /**
     * @dataProvider specificityProvider
     */
    public function testSpecificity(Selector $selector, Specificity $expected)
    {
        SelectorAssert::specificityEquals($selector, $expected);
    }

    abstract public function specificityProvider(): iterable;
}
