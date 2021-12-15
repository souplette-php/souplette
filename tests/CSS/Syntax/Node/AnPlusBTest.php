<?php declare(strict_types=1);

namespace Souplette\Tests\CSS\Syntax\Node;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Souplette\CSS\Syntax\Node\AnPlusB;

final class AnPlusBTest extends TestCase
{
    /**
     * @dataProvider toStringProvider
     */
    public function testToString(AnPlusB $input, string $expected)
    {
        Assert::assertSame($expected, (string)$input);
    }

    public function toStringProvider(): iterable
    {
        yield [new AnPlusB(0, 1), '1'];
        yield [new AnPlusB(0, 2), '2'];
        yield [new AnPlusB(2, 1), 'odd'];
        yield [new AnPlusB(2, 0), 'even'];
        yield [new AnPlusB(3, 0), '3n'];
        yield [new AnPlusB(3, 1), '3n+1'];
        yield [new AnPlusB(-1, 1), '-n+1'];
        yield [new AnPlusB(-2, -1), '-2n-1'];
    }
}
